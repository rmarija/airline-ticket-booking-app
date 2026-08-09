import React, { useEffect, useState, useContext } from "react";
import { useParams, useNavigate } from "react-router-dom";
import flightService from "../api/flightService";
import { AuthContext } from "../context/AuthContext";
import { FaPlane } from "react-icons/fa";
import "./FlightDetails.css";

const FlightDetails = () => {
  const { id } = useParams();
  const navigate = useNavigate();

  const [flight, setFlight] = useState(null);
  const [loading, setLoading] = useState(true);

  const { user } = useContext(AuthContext);

  useEffect(() => {
    const fetchFlight = async () => {
      try {
        const response = await flightService.getFlight(id);
        setFlight(response.data);
      } catch (error) {
        console.error("Greška prilikom dohvatanja leta:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchFlight();
  }, [id]);

  if (loading) {
    return <p className="loading">Učitavanje...</p>;
  }

  if (!flight) {
    return <p className="error">Let nije pronađen.</p>;
  }

  const handleReservationClick = () => {
    if (!user) {
      navigate("/login");
    } else {
      navigate(`/rezervacija/${flight.id}`);
    }
  };

  return (
    <div className="flight-details-page">
      <div className="boarding-pass">
        <div className="boarding-pass-header">
          <span className="airline-tag">✈️ Veloro AvioKarte</span>
          <span className="flight-number">{flight.broj_leta}</span>
        </div>

        <div className="boarding-pass-route">
          <div className="route-city">
            <span className="city-name">{flight.polaziste}</span>
            <span className="route-time">{flight.vreme_poletanja.split(" ")[1]}</span>
          </div>
          <div className="route-path">
<FaPlane className="route-plane" />
          </div>
          <div className="route-city">
            <span className="city-name">{flight.odrediste}</span>
            <span className="route-time">{flight.vreme_sletanja.split(" ")[1]}</span>
          </div>
        </div>

        <div className="boarding-pass-meta">
          <div className="meta-item">
            <span className="meta-label">Datum</span>
            <span className="meta-value">{flight.vreme_poletanja.split(" ")[0]}</span>
          </div>
          <div className="meta-item">
            <span className="meta-label">Broj leta</span>
            <span className="meta-value">{flight.broj_leta}</span>
          </div>
        </div>

        <div className="boarding-pass-stub">
          <div className="stub-notch stub-notch-left"></div>
          <div className="stub-notch stub-notch-right"></div>

          <div className="stub-price">
            <span className="meta-label">Cena</span>
            <span className="price-value">{flight.cena} €</span>
          </div>

          <button onClick={handleReservationClick} className="reserve-button">
            Rezerviši <span>✈️</span>
          </button>
        </div>
      </div>
    </div>
  );
};

export default FlightDetails;

