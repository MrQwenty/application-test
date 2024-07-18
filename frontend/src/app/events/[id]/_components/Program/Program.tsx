'use client';

import React from 'react';
import { List, Card } from 'antd';
import { SpeechInterface } from '../../../../../types/DataModelTypes/SpeechInterface';
import { formatTime } from '../../../../../lib/Utility';

interface ProgramProps {
    program: SpeechInterface[];
}

const Program: React.FC<ProgramProps> = ({ program }) => {
    return (
        <Card title="Program">
            <List
                dataSource={program}
                renderItem={speech => (
                    <List.Item key={speech.startTime}>
                        <List.Item.Meta
                            title={speech.topic}
                            description={`Speaker: ${speech.speaker} | Time: ${formatTime(speech.startTime.toString())} - ${formatTime(speech.endTime.toString())}`}
                        />
                    </List.Item>
                )}
            />
        </Card>
    );
};

export default Program;
