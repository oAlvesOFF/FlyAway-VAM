import { LazyStore } from '@tauri-apps/plugin-store';
import { invoke } from '@tauri-apps/api/core';

const store = new LazyStore('settings.json');

export const saveApiKey = async (apiKey: string) => {
  await store.set('api_key', { value: apiKey });
  await store.save();
  await invoke('set_api_key', { key: apiKey });
};

export const getApiKey = async (): Promise<string | null> => {
  const val = await store.get<{ value: string }>('api_key');
  return val ? val.value : null;
};

export const loadAndSyncApiKey = async () => {
  const val = await store.get<{ value: string }>('api_key');
  if (val) {
    await invoke('set_api_key', { key: val.value });
  }
};
