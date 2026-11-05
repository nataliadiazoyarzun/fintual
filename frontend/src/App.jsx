import { useState } from 'react';
import { rebalance } from './api.js';

// Temporary sample data until PortfolioForm exists.
const SAMPLE_STOCKS = [
  { ticker: 'META', quantity: 10, price: 100 },
  { ticker: 'AAPL', quantity: 10, price: 100 },
];
const SAMPLE_ALLOCATION = { META: 40, AAPL: 60 };

export default function App() {
  const [stocks] = useState(SAMPLE_STOCKS);
  const [allocation] = useState(SAMPLE_ALLOCATION);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [result, setResult] = useState(null);

  async function handleRebalance() {
    setLoading(true);
    setError(null);
    setResult(null);

    try {
      setResult(await rebalance({ stocks, allocation }));
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <main>
      <h1>Portfolio Rebalancer</h1>

      <button onClick={handleRebalance} disabled={loading}>
        Rebalancear
      </button>

      {loading && <p>Cargando...</p>}
      {error && <p role="alert">Error: {error}</p>}
      {result && <pre>{JSON.stringify(result, null, 2)}</pre>}
    </main>
  );
}
