'use client';

import { useRef, useState } from 'react';

type VoiceChatRecorderProps = {
  listingId: string;
  receiverId?: string;
};

const API_BASE = process.env.NEXT_PUBLIC_API_URL ?? 'http://127.0.0.1:8000/api/v1';

export default function VoiceChatRecorder({ listingId, receiverId }: VoiceChatRecorderProps) {
  const mediaRecorderRef = useRef<MediaRecorder | null>(null);
  const chunksRef = useRef<Blob[]>([]);
  const [isRecording, setIsRecording] = useState(false);
  const [status, setStatus] = useState('Tap to record inquiry message');

  const startRecording = async () => {
    if (!receiverId) {
      setStatus('Provider chat unavailable for this listing right now.');
      return;
    }

    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    const recorder = new MediaRecorder(stream);

    chunksRef.current = [];
    recorder.ondataavailable = (event) => chunksRef.current.push(event.data);
    recorder.onstop = async () => {
      const blob = new Blob(chunksRef.current, { type: 'audio/webm' });
      const reader = new FileReader();
      const audioDataUrl = await new Promise<string>((resolve) => {
        reader.onloadend = () => resolve((reader.result as string) ?? '');
        reader.readAsDataURL(blob);
      });

      setStatus('Uploading and transcribing voice message...');

      await fetch(`${API_BASE}/chat/messages/voice`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          listing_id: listingId,
          receiver_id: receiverId,
          audio_url: audioDataUrl,
          target_locale: 'en',
        }),
      });

      setStatus('Voice message queued for transcription and translation.');
    };

    mediaRecorderRef.current = recorder;
    recorder.start();
    setIsRecording(true);
    setStatus('Recording...');
  };

  const stopRecording = () => {
    mediaRecorderRef.current?.stop();
    setIsRecording(false);
  };

  return (
    <div className="voice-recorder-card">
      <strong>Pre-booking voice inquiry</strong>
      <p>{status}</p>
      <div className="voice-recorder-actions">
        {!isRecording ? (
          <button className="btn btn-primary" onClick={startRecording}>Start recording</button>
        ) : (
          <button className="btn btn-secondary" onClick={stopRecording}>Stop and send</button>
        )}
      </div>
    </div>
  );
}
