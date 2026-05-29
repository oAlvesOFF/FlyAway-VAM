import { getApiKey } from './authService';

const BASE_URL = 'https://v1.flyazoresvirtual.com/api';

export const authenticatedFetch = async (endpoint: string, options: RequestInit = {}) => {
  const apiKey = await getApiKey();
  
  const headers = {
    'Authorization': `Bearer ${apiKey}`,
    'Content-Type': 'application/json',
    ...options.headers,
  };

  const response = await fetch(`${BASE_URL}${endpoint}`, { ...options, headers });
  
  if (!response.ok) {
    throw new Error(`API Error: ${response.statusText}`);
  }

  return response.json();
};
