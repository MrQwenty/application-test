'use client';

import { useQuery } from "@apollo/client";
import { Card, List, Spin, Alert } from "antd";
import scheduleQuery from "./queries/scheduleQuery";
import dayjs from "dayjs";
import { SubsetInterface } from "../../../types/DataModelTypes/SubsetInterface";
import { EventInterface } from "../../../types/DataModelTypes/EventInterface";
import Link from "next/link";

export default function Schedule() {

    const { loading, error, data } = useQuery<{ eventsSubset: SubsetInterface<EventInterface> }>(
        scheduleQuery,
        {
            fetchPolicy: "network-only",
            nextFetchPolicy: "cache-first",
            variables: {
                limit: 10,
                offset: 0,
                startDate: dayjs().startOf("day").toISOString()
            }
        }
    );

    if (loading) return <Spin tip="Loading..." />;
    if (error) return <Alert message="Error" description={error.message} type="error" showIcon />;

    return (
        <Card>
            <List
                dataSource={data?.eventsSubset.items ?? []}
                renderItem={event => (
                    <List.Item
                        key={event.id}
                        // eslint-disable-next-line react/jsx-key
                        actions={[<Link href={`/events/${encodeURIComponent(event.id)}`}>Detail</Link>]}
                    >
                        <List.Item.Meta
                            title={<>{dayjs(event.date).format('L')} - {event.name}</>}
                            description={event.program?.map(speech => speech.speaker).join(", ")}
                        />
                    </List.Item>
                )}
            />
        </Card>
    );
}