import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import flightService from "../api/flightService";
import Button from "../components/ui/Button";
import Input from "../components/ui/Input";
import Card from "../components/ui/Card"
import CityAutocomplete from "../components/ui/CityAutocomplete";;
import Hero from "../components/ui/Hero";
import "./Home.css";  

const Home = () => {
  const [polazna, setPolazna] = useState("");
  const [odrediste, setOdrediste] = useState("");
  const [datum, setDatum] = useState("");
  const navigate = useNavigate();

  const handleSearch = async () => {
    try {
      const response = await flightService.getAllFlights();
      const flightsArray = response.data.data;

      const filteredFlights = flightsArray.filter((letObj) => {
        const letDatum = letObj.vreme_poletanja.split(" ")[0];
        return (
          letObj.polaziste.toLowerCase().includes(polazna.toLowerCase()) &&
          letObj.odrediste.toLowerCase().includes(odrediste.toLowerCase()) &&
          (datum === "" || letDatum === datum)
        );
      });

      navigate("/rezultati", { state: { flights: filteredFlights } });
    } catch (error) {
      console.error("Greška prilikom pretrage:", error);
    }
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
