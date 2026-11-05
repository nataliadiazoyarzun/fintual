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
    <>
      <a className="visually-hidden-focusable skip-link p-2 d-inline-block" href="#contenido">
        Saltar al contenido
      </a>

      <header className="app-header py-4 py-md-5">
        <div className="container app-container">
          <p className="app-eyebrow mb-2">Natalia Díaz · Senior Software Engineer</p>
          <h1 className="display-6 fw-bold mb-1">Portfolio Rebalancer</h1>
          <p className="app-subtitle mb-3">Prueba técnica desarrollada para Fintual.</p>
          <nav aria-label="Enlaces del proyecto">
            <ul className="list-inline mb-0">
              <li className="list-inline-item me-3">
                <a className="app-link" href="https://ndiaz.dev" target="_blank" rel="noopener noreferrer">
                  Portafolio profesional (ndiaz.dev)
                  <span className="visually-hidden"> (se abre en una pestaña nueva)</span>
                </a>
              </li>
              <li className="list-inline-item">
                <a
                  className="app-link"
                  href="https://github.com/nataliadiazoyarzun/portfolio-rebalancer"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  Repositorio en GitHub
                  <span className="visually-hidden"> (se abre en una pestaña nueva)</span>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </header>

      <main id="contenido" className="container app-container py-4 py-md-5">
        <div className="card mb-4">
          <div className="card-header fw-semibold">Tu portafolio</div>
          <div className="card-body">
            <PortfolioForm
              rows={rows}
              onChange={setRows}
              onSubmit={handleRebalance}
              loading={loading}
            />
          </div>
        </div>

        {loading && (
          <p className="text-secondary" role="status">
            Cargando...
          </p>
        )}
        {error && (
          <div className="alert alert-danger" role="alert">
            Error: {error}
          </div>
        )}
        {/* The live region exists before the result so screen readers announce it. */}
        <div aria-live="polite">{result && <RecommendationsTable result={result} />}</div>
      </main>

      <footer className="app-footer py-4">
        <div className="container app-container small">
          Desarrollado por <a className="app-link" href="https://ndiaz.dev" target="_blank" rel="noopener noreferrer">Natalia Díaz<span className="visually-hidden"> (se abre en una pestaña nueva)</span></a>
        </div>
      </footer>
    </>
  );
}
