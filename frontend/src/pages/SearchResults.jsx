import React, { useState, useEffect } from "react";
import { useSearchParams } from "react-router-dom";
import FlightCard from "../components/ui/FlightCard";
import Breadcrumbs from "../components/ui/Breadcrumbs";
import CityAutocomplete from "../components/ui/CityAutocomplete";
import { getFlights } from "../api/flightService";
import "./SearchResults.css";

const SearchResults = () => {
  const [searchParams, setSearchParams] = useSearchParams();

  const qPolazna = searchParams.get("polaziste") || "";
  const qOdrediste = searchParams.get("odrediste") || "";
  const qDatum = searchParams.get("datum") || "";

  const [polazna, setPolazna] = useState(qPolazna);
  const [odrediste, setOdrediste] = useState(qOdrediste);
  const [datum, setDatum] = useState(qDatum);

  const [flights, setFlights] = useState([]);
  const [searching, setSearching] = useState(false);

  const [priceFilter, setPriceFilter] = useState([0, 10000]);
  const [sortOrder, setSortOrder] = useState("asc");
  const [page, setPage] = useState(1);
  const resultsPerPage = 5;

  useEffect(() => {
    setPolazna(qPolazna);
    setOdrediste(qOdrediste);
    setDatum(qDatum);

    setSearching(true);

    const params = { per_page: 100 };
    if (qPolazna) params.polaziste = qPolazna;
    if (qOdrediste) params.odrediste = qOdrediste;

    getFlights(params)
      .then((res) => {
        let lista = Array.isArray(res.data?.data) ? res.data.data : [];

        if (qDatum) {
          lista = lista.filter(
            (l) => String(l.vreme_poletanja).slice(0, 10) === qDatum
          );
        }

        setFlights(lista);
        setPage(1);
      })
      .catch((err) => {
        console.error("Greška prilikom pretrage:", err);
        setFlights([]);
      })
      .finally(() => setSearching(false));
  }, [qPolazna, qOdrediste, qDatum]);

  const handleResearch = () => {
    const params = {};
    if (polazna) params.polaziste = polazna;
    if (odrediste) params.odrediste = odrediste;
    if (datum) params.datum = datum;
    setSearchParams(params);
  };

  let filteredFlights = flights.filter(
    (f) => Number(f.cena) >= priceFilter[0] && Number(f.cena) <= priceFilter[1]
  );

  filteredFlights = [...filteredFlights].sort((a, b) =>
    sortOrder === "asc" ? a.cena - b.cena : b.cena - a.cena
  );

  const totalPages = Math.ceil(filteredFlights.length / resultsPerPage);
  const displayedFlights = filteredFlights.slice(
    (page - 1) * resultsPerPage,
    page * resultsPerPage
  );

  return (
    <div className="search-results">
      <Breadcrumbs
        items={[{ label: "Početna", to: "/" }, { label: "Rezultati pretrage" }]}
      />

      <h2>Rezultati pretrage</h2>

      <div className="research-box">
        <h3>Pretraga</h3>
        <div className="research-fields">
          <CityAutocomplete
            label="Polazna destinacija"
            placeholder="Unesite polaznu destinaciju"
            value={polazna}
            onChange={setPolazna}
          />
          <CityAutocomplete
            label="Odredište"
            placeholder="Unesite odredište"
            value={odrediste}
            onChange={setOdrediste}
          />
          <div className="research-date">
            <label>Datum (opciono)</label>
            <input
              type="date"
              value={datum}
              onChange={(e) => setDatum(e.target.value)}
            />
          </div>
        </div>
        <button
          className="research-button"
          onClick={handleResearch}
          disabled={searching}
        >
          {searching ? "Pretražujem..." : "Pretraži ponovo"}
        </button>
      </div>

      <div className="filter-box">
        <h3>Filtriraj po ceni</h3>
        <div className="flex-filters">
          <div>
            <label>Cena od</label>
            <input
              type="number"
              value={priceFilter[0]}
              onChange={(e) =>
                setPriceFilter([Number(e.target.value), priceFilter[1]])
              }
            />
          </div>
          <div>
            <label>Cena do</label>
            <input
              type="number"
              value={priceFilter[1]}
              onChange={(e) =>
                setPriceFilter([priceFilter[0], Number(e.target.value)])
              }
            />
          </div>
        </div>

        <h3>Sortiraj po ceni</h3>
        <select value={sortOrder} onChange={(e) => setSortOrder(e.target.value)}>
          <option value="asc">Cena (najniža prvo)</option>
          <option value="desc">Cena (najviša prvo)</option>
        </select>
      </div>

      {searching ? (
        <p className="no-results">Učitavanje...</p>
      ) : displayedFlights.length === 0 ? (
        <p className="no-results">Nema letova za zadate kriterijume</p>
      ) : (
        <div className="results-list">
          {displayedFlights.map((letData) => (
            <FlightCard key={letData.id} flight={letData} />
          ))}
        </div>
      )}

      {totalPages > 1 && (
        <div className="pagination">
          <button
            disabled={page === 1}
            onClick={() => {
              setPage(page - 1);
              window.scrollTo({ top: 0, behavior: "smooth" });
            }}
          >
            ⬅ Prethodna
          </button>
          <span>
            Stranica {page} od {totalPages}
          </span>
          <button
            disabled={page === totalPages}
            onClick={() => {
              setPage(page + 1);
              window.scrollTo({ top: 0, behavior: "smooth" });
            }}
          >
            Sledeća ➡
          </button>
        </div>
      )}
    </div>
  );
};

export default SearchResults;