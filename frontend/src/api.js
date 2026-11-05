const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8000';

export async function rebalance(payload) {
  let response;

  try {
    response = await fetch(`${API_URL}/rebalance`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
  } catch {
    throw new Error('No se pudo conectar con el servidor.');
  }

  const data = await response.json().catch(() => null);

  if (!response.ok) {
    throw new Error(data?.error ?? `Error inesperado (${response.status}).`);
  }

  return data;
}
