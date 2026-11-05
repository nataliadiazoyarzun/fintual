import { useState } from 'react';
import { rebalance } from './api.js';
import PortfolioForm, { newRow } from './components/PortfolioForm.jsx';
import RecommendationsTable from './components/RecommendationsTable.jsx';

function initialRows() {
  return [
    newRow({ ticker: 'META', quantity: '10', price: '100', target: '40' }),
    newRow({ ticker: 'AAPL', quantity: '10', price: '100', target: '60' }),
  ];
}

// Only converts form text to JSON numbers; every rule is validated by the backend.
function buildPayload(rows) {
  return {
    stocks: rows.map((row) => ({
      ticker: row.ticker,
      quantity: Number(row.quantity),
      price: Number(row.price),
    })),
    allocation: Object.fromEntries(rows.map((row) => [row.ticker, Number(row.target)])),
  };
}

export default function App() {
  const [rows, setRows] = useState(initialRows);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [result, setResult] = useState(null);

  async function handleRebalance() {
    setLoading(true);
    setError(null);
    setResult(null);

    try {
      setResult(await rebalance(buildPayload(rows)));
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <main>
      <h1>Portfolio Rebalancer</h1>

      <PortfolioForm
        rows={rows}
        onChange={setRows}
        onSubmit={handleRebalance}
        loading={loading}
      />

      {loading && <p>Cargando...</p>}
      {error && <p role="alert">Error: {error}</p>}
      {result && <RecommendationsTable result={result} />}
    </main>
  );
}
