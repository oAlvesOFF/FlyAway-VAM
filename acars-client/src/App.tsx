import { useEffect } from 'react';
import { listen } from '@tauri-apps/api/event';
import { AppLayout } from './components/AppLayout';

function App() {
  useEffect(() => {
    const unlisten = listen('telemetry-updated', (event) => {
      console.log('[telemetry]', event.payload);
    });
    return () => { unlisten.then(fn => fn()); };
  }, []);

  return <AppLayout />;
}

export default App;
