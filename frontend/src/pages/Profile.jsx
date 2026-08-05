import React, { useContext, useEffect, useState } from "react";
import { AuthContext } from "../context/AuthContext";
import FlightCard from "../components/ui/FlightCard";
import axios from "axios";
import Breadcrumbs from "../components/ui/Breadcrumbs";
import { useNavigate } from "react-router-dom";
import "./Profile.css";

const Profile = () => {
  const { user, token, logout } = useContext(AuthContext);
  const [reservations, setReservations] = useState([]);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate("/");
  };

  useEffect(() => {
    if (user && token) {
      axios
        .get("http://localhost:8000/api/rezervacije", {
          headers: { Authorization: `Bearer ${token}` },
        })
        .then((res) => {
          console.log("Rezervacije sa backend-a:", res.data);
          setReservations(res.data.data || []);
        })
        .catch((err) =>
          console.error("Greška pri učitavanju rezervacija:", err)
        )
        .finally(() => setLoading(false));
    }
  }, [user, token]);

  const handleDelete = async (id) => {
    if (
      !window.confirm("Da li ste sigurni da želite da obrišete ovu rezervaciju?")
    )
      return;
    try {
      await axios.delete(`http://localhost:8000/api/rezervacije/${id}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setReservations((prev) => prev.filter((rez) => rez.id !== id));
    } catch (error) {
      console.error("Greška pri brisanju rezervacije:", error);
    }
  };

  if (!user) return <p>Morate biti prijavljeni da vidite profil.</p>;

  return (
    <div className="max-w-4xl mx-auto mt-10">
      <Breadcrumbs
        items={[{ label: "Početna", to: "/" }, { label: "Profil" }]}
      />

      <div className="profile-header">
        <div className="user-info">
          <div className="avatar">{user?.name?.charAt(0)}</div>
          <div>
            <p className="user-name">{user.name}</p>
            <p className="user-email">{user.email}</p>
          </div>
        </div>
        <button onClick={handleLogout} className="logout-btn">
          Odjavi se
        </button>
      </div>

      <h3 className="section-title">Moje rezervacije</h3>

      {loading ? (
        <p>Učitavanje rezervacija...</p>
      ) : reservations.length === 0 ? (
        <p>Nemate nijednu rezervaciju.</p>
      ) : (
        <ul className="reservations-list">
          {reservations.map((rez) => (
            <li key={rez.id} className="reservation-card">
              {rez.let && <FlightCard flight={rez.let} />}

             <div className="reservation-details">
  <p>
    <strong><span role="img" aria-label="putnik">👤</span> Ime putnika:</strong> {rez.ime_putnika}
  </p>
  <p>
    <strong><span role="img" aria-label="sedište">💺</span> {rez.broj_karata > 1 ? "Broj sedišta:" : "Sedište:"}</strong>{" "}
    {Array.isArray(rez.broj_sedista)
      ? rez.broj_sedista.join(", ")
      : rez.broj_sedista}
  </p>
  <p>
    <strong><span role="img" aria-label="karte">🎟</span> Broj karata:</strong> {rez.broj_karata}
  </p>
  {rez.ukupna_cena && (
    <p>
      <strong><span role="img" aria-label="cena">💶</span> Ukupna cena:</strong>{" "}
      {parseFloat(rez.ukupna_cena).toFixed(2)} €
    </p>
  )}

  <button
    onClick={() => handleDelete(rez.id)}
    className="delete-btn"
  >
    🗑 Obriši rezervaciju
  </button>
</div>

            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

export default Profile;
