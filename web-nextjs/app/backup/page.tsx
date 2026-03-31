import type { Metadata } from 'next';
import LegacyHomePage from '../../components/legacy/legacy-home-page';

export const metadata: Metadata = {
  title: 'PearlHub Pro Legacy UI Backup',
  description: 'Backup copy of the previous PearlHub homepage UI.'
};

export default function BackupPage() {
  return <LegacyHomePage />;
}