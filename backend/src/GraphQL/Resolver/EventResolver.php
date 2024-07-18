<?php

declare(strict_types=1);

namespace App\GraphQL\Resolver;

use App\Document\Event;
use App\DocumentModel\EventModel;
use App\Service\GraphQL\Buffer;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use \ArrayObject;
use App\Service\GlobalId\GlobalIdProvider;
use App\Utility\MethodsBuilder;
use GraphQL\Deferred;
use App\Service\GraphQL\FieldEncryptionProvider;
use Doctrine\Common\Collections\Collection;
use App\Service\GraphQL\GetByFieldValuesQueryArgumentsProvider;

use function in_array;

final class EventResolver implements ResolverInterface
{
    /**
     * Here we store all fields which need a 'complex' field resolver with custom logic.
     * All other fields will be resolved by calling the getter method on the object
     */
    private const COMPLEX_RESOLVER_FIELDS = [
        'id',
        'version',
        'participants',
        'program'
    ];

    public function __construct(
        private EventModel $eventModel,
        private GlobalIdProvider $globalIdProvider,
        private Buffer $buffer,
        private FieldEncryptionProvider $fieldEncryptionProvider,
        private GetByFieldValuesQueryArgumentsProvider $getByFieldValuesQueryArgumentsProvider
    ) {
    }

    public function resolveOneByKey(string $key): Deferred
    {
        $buffer = $this->buffer;
        $model = $this->eventModel;
        $getByFieldValuesQueryArgumentsProvider = $this->getByFieldValuesQueryArgumentsProvider;
        $buffer->add(Event::class, 'key', $key);

        return new Deferred(fn () => $buffer->get(
            Event::class,
            'key',
            $key,
            function ($keys) use ($model, $getByFieldValuesQueryArgumentsProvider): Collection {
                $queryCriteria = $getByFieldValuesQueryArgumentsProvider->toQueryCriteria('key', $keys);
                return $model->getRepository()->find($queryCriteria);
            }
        ));
    }

    /**
     * This magic method is called to resolve each field of the Event type. 
     * @param  Event $event
     */
    public function __invoke(ResolveInfo $info, $event, ArgumentInterface $args, ArrayObject $context): mixed
    {
        if (!in_array($info->fieldName, static::COMPLEX_RESOLVER_FIELDS)) {
            $getterMethodForField = MethodsBuilder::toGetMethod($info->fieldName);
            return $event->$getterMethodForField();
        }
        $getterMethodForField = MethodsBuilder::toResolveMethod($info->fieldName);
        return $this->$getterMethodForField($event, $args);
    }

    private function resolveId(Event $event): string
    {
        return $this->globalIdProvider->toGlobalId($event);
    }

    private function resolveVersion(Event $event): string
    {
        $version = (string) $event->getVersion();
        $id = $event->getId();
        return $this->fieldEncryptionProvider->encrypt($version, $id);
    }

    private function resolveParticipants(Event $event, ArgumentInterface $args): array
    {
        $queryString = $args['queryString'] ?? null;
        $participants = $event->getParticipants();

        if ($queryString) {
            $queryString = strtolower($queryString);
            return array_filter($participants, fn($participant) => stripos($participant->getName(), $queryString) !== false);
        }

        return $participants;
    }

    private function resolveProgram(Event $event): Collection
    {
        $program = $event->getProgram();
        $iterator = $program->getIterator();

        $iterator->uasort(function ($a, $b) {
            return ($a->getStartTime() < $b->getStartTime()) ? -1 : 1;
        });

        return new ArrayCollection(iterator_to_array($iterator));
    }
}
