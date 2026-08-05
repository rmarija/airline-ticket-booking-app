import React from "react";
import { useNavigate } from "react-router-dom";
import "./FlightCard.css";

const FlightCard = ({ flight }) => {
  const navigate = useNavigate();

  return (
    <div className="flight-card">
      <h3>
        {flight.polaziste} → {flight.odrediste}
      </h3>

      <p>
        Datum polaska: <strong>{flight.vreme_poletanja.split(" ")[0]}</strong>
      </p>

      <p>
        Poletanje: <strong>{flight.vreme_poletanja.split(" ")[1]}</strong> |{" "}
        Sletanje: <strong>{flight.vreme_sletanja.split(" ")[1]}</strong>
      </p>

      <div className="price">{flight.cena} €</div>

      <button onClick={() => navigate(`/letovi/${flight.id}`)}>
        <span className="btn-text">Pogledaj detalje</span>
        <span className="plane-icon">✈️</span>
      </button>
    </div>
  );
};

export default FlightCard;