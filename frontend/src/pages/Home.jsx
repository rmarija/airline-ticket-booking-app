import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import Button from "../components/ui/Button";
import Input from "../components/ui/Input";
import Card from "../components/ui/Card";
import CityAutocomplete from "../components/ui/CityAutocomplete";
import Hero from "../components/ui/Hero";
import "./Home.css";

const Home = () => {
  const [polazna, setPolazna] = useState("");
  const [odrediste, setOdrediste] = useState("");
  const [datum, setDatum] = useState("");
  const navigate = useNavigate();

  const handleSearch = () => {
    const params = new URLSearchParams();
    if (polazna) params.set("polaziste", polazna);
    if (odrediste) params.set("odrediste", odrediste);
    if (datum) params.set("datum", datum);
    navigate(`/rezultati?${params.toString()}`);
  };

  return (
    <>
      <Hero />

      <div id="search-form" className="search-wrapper">
        <Card className="search-card">
          <h2 className="search-title">Pretraži letove</h2>

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

          <Input
            label="Datum polaska (opciono)"
            type="date"
            value={datum}
            onChange={(e) => setDatum(e.target.value)}
          />
          <Button onClick={handleSearch} className="search-button">
            Pretraži
          </Button>
        </Card>
      </div>
    </>
  );
};

export default Home;