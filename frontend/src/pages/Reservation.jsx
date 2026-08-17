import React, { useState, useEffect, useContext } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { AuthContext } from "../context/AuthContext";
import axios from "axios";
import { FaUser, FaEnvelope, FaPlaneArrival } from "react-icons/fa";
import SeatMap from "../components/SeatMap/SeatMap";
import "./Reservation.css";

const Reservation = () => {
  const { id: letId } = useParams();
  const navigate = useNavigate();
  const { user, token } = useContext(AuthContext);

  const seatPrices = { 1: 5, 2: 5, 3: 10, 4: 10, 5: 15 };
  const defaultExtra = 5;

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const [letInfo, setLetInfo] = useState(null);
  const [slobodnaSedistaOdlazak, setSlobodnaSedistaOdlazak] = useState([]);
  const [zauzetaSedistaOdlazak, setZauzetaSedistaOdlazak] = useState([]);

  const [returnFlights, setReturnFlights] = useState([]);
  const [selectedReturn, setSelectedReturn] = useState(null);
  const [slobodnaSedistaPovratak, setSlobodnaSedistaPovratak] = useState([]);
  const [zauzetaSedistaPovratak, setZauzetaSedistaPovratak] = useState([]);
  const [submitting, setSubmitting] = useState(false); 
  const [brojKarata, setBrojKarata] = useState(1);
  const [birajSedišta, setBirajSedišta] = useState(false);
  const [putnici, setPutnici] = useState([
    { ime: user?.name || "", email: user?.email || "", sedisteOdlazak: null, sedistePovratak: null },
  ]);

  const doplataOdlazak = birajSedišta
    ? putnici.reduce((sum, p) => sum + (p.sedisteOdlazak ? (seatPrices[p.sedisteOdlazak] ?? defaultExtra) : 0), 0)
    : 0;

  const doplataPovratak = selectedReturn && birajSedišta
    ? putnici.reduce((sum, p) => sum + (p.sedistePovratak ? (seatPrices[p.sedistePovratak] ?? defaultExtra) : 0), 0)
    : 0;

  const cenaOdlazak = letInfo ? (letInfo.cena || 0) * brojKarata : 0;
  const cenaPovratak = selectedReturn ? (selectedReturn.cena || 0) * brojKarata : 0;

  const ukupnaCena = cenaOdlazak + cenaPovratak + doplataOdlazak + doplataPovratak;

  useEffect(() => {
    const run = async () => {
      if (!user) return;
      try {
        const resLet = await axios.get(`http://localhost:8000/api/letovi/${letId}`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        const letData = resLet.data;
        setLetInfo(letData);

        const resSeats = await axios.get(
          `http://localhost:8000/api/slobodna-sedista?let_id=${letId}`,
          { headers: { Authorization: `Bearer ${token}` } }
        );
        const slobodna = resSeats.data.slobodna_sedista || [];
        setSlobodnaSedistaOdlazak(slobodna);

        const allSeats = Array.from({ length: letData.broj_mesta }, (_, i) => i + 1);
        setZauzetaSedistaOdlazak(allSeats.filter((s) => !slobodna.includes(s)));

        const resReturn = await axios.get(
          `http://localhost:8000/api/letovi`,
          {
            headers: { Authorization: `Bearer ${token}` },
            params: {
              polaziste: letData.odrediste,
              odrediste: letData.polaziste,
            },
          }
        );
        const list = resReturn.data?.data || resReturn.data || [];
        setReturnFlights(list);
      } catch (err) {
        console.error(err);
        setError("Neuspešno učitavanje leta ili sedišta.");
      } finally {
        setLoading(false);
      }
    };
    run();
  }, [letId, token, user]);

  useEffect(() => {
    const next = [...putnici];
    while (next.length < brojKarata) next.push({ ime: "", email: "", sedisteOdlazak: null, sedistePovratak: null });
    while (next.length > brojKarata) next.pop();

    if (next[0]) {
      next[0].ime = user?.name || "";
      next[0].email = user?.email || "";
    }
    setPutnici(next);
  }, [brojKarata, user]);

  useEffect(() => {
    const fetchReturnSeats = async () => {
      if (!selectedReturn) {
        setSlobodnaSedistaPovratak([]);
        setZauzetaSedistaPovratak([]);
        return;
      }
      try {
        const resSeats = await axios.get(
          `http://localhost:8000/api/slobodna-sedista?let_id=${selectedReturn.id}`,
          { headers: { Authorization: `Bearer ${token}` } }
        );
        const slobodna = resSeats.data.slobodna_sedista || [];
        setSlobodnaSedistaPovratak(slobodna);

        const allSeats = Array.from({ length: selectedReturn.broj_mesta }, (_, i) => i + 1);
        setZauzetaSedistaPovratak(allSeats.filter((s) => !slobodna.includes(s)));

        setPutnici((prev) => prev.map((p) => ({ ...p, sedistePovratak: null })));
      } catch (err) {
        console.error(err);
        setSlobodnaSedistaPovratak([]);
        setZauzetaSedistaPovratak([]);
      }
    };
    fetchReturnSeats();
  }, [selectedReturn, token]);

  const handleSeatToggle = (seat, type) => {
    setPutnici((prev) => {
      const next = prev.map(p => ({ ...p }));
      const key = type === "odlazak" ? "sedisteOdlazak" : "sedistePovratak";

      const hasSeatIndex = next.findIndex((p) => p[key] === seat);
      
      if (hasSeatIndex !== -1) {
        next[hasSeatIndex][key] = null;
      } else {
        const emptyIndex = next.findIndex((p) => !p[key]);
        if (emptyIndex !== -1) {
          next[emptyIndex][key] = seat;
        } else {
          alert(`Već ste izabrali maksimalan broj sedišta (${brojKarata}) za ${type}!`);
        }
      }
      return next;
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    if (submitting) return;
    setSubmitting(true); 

    if (!user) {
      setError("Morate biti prijavljeni.");
      return;
    }

    try {
      let seatsOdlazak = [];
      if (birajSedišta) {
        seatsOdlazak = putnici.map((p) => Number(p.sedisteOdlazak)).filter((x) => Number.isInteger(x) && x > 0);
      } else {
        seatsOdlazak = slobodnaSedistaOdlazak.slice(0, brojKarata);
      }

      if (seatsOdlazak.length !== brojKarata) {
        setError("Morate izabrati sedišta (ili dozvoliti automatski izbor) za sve putnike (ODLAZAK).");
        return;
      }
      
      if (birajSedišta) {
        for (const s of seatsOdlazak) {
          await axios.post(
            "http://localhost:8000/api/zakljucaj-sediste",
            { let_id: letId, broj_sedista: s },
            { headers: { Authorization: `Bearer ${token}` } }
          );
        }
      }

      for (let i = 0; i < brojKarata; i++) {
        await axios.post(
          "http://localhost:8000/api/rezervacije",
          {
            ime_putnika: putnici[i].ime,
            email: putnici[i].email,
            let_id: letId,
            broj_sedista: [seatsOdlazak[i]], 
          },
          { headers: { Authorization: `Bearer ${token}` } }
        );
      }

      if (selectedReturn) {
        let seatsPovratak = [];
        if (birajSedišta) {
          seatsPovratak = putnici
            .map((p) => Number(p.sedistePovratak))
            .filter((x) => Number.isInteger(x) && x > 0);
        } else {
          seatsPovratak = slobodnaSedistaPovratak.slice(0, brojKarata);
        }

        if (seatsPovratak.length !== brojKarata) {
          setError("Morate izabrati sedišta (ili automatski izbor) za sve putnike (POVRATAK).");
          return;
        }

        if (birajSedišta) {
          for (const s of seatsPovratak) {
            await axios.post(
              "http://localhost:8000/api/zakljucaj-sediste",
              { let_id: selectedReturn.id, broj_sedista: s },
              { headers: { Authorization: `Bearer ${token}` } }
            );
          }
        }

        for (let i = 0; i < brojKarata; i++) {
          await axios.post(
            "http://localhost:8000/api/rezervacije",
            {
              ime_putnika: putnici[i].ime,
              email: putnici[i].email,
              let_id: selectedReturn.id,
              broj_sedista: [seatsPovratak[i]], 
            },
            { headers: { Authorization: `Bearer ${token}` } }
          );
        }
      }

      navigate("/success");
    } catch (err) {
      console.error(err);
      setError(err.response?.data?.error || "Greška pri rezervaciji. Proverite sedišta i pokušajte ponovo.");
    }finally {
  setSubmitting(false);
}

  };

  if (loading) return <p className="loading">Učitavanje...</p>;
  if (!user) return <p className="error">Morate biti prijavljeni da rezervišete kartu.</p>;

  return (
    <div className="reservation-container">
      <div className="reservation-card">
  <h2>Rezervacija leta {letInfo?.broj_leta}</h2>

  <div className="flight-route-summary">
    <div className="route-cities">
      <span className="city-name">{letInfo?.polaziste}</span>
      <span className="route-plane-icon">✈</span>
      <span className="city-name">{letInfo?.odrediste}</span>
    </div>
    <div className="flight-departure-time">
      <span>📅 {letInfo?.vreme_poletanja?.split(" ")[0]}</span>
      <span className="dot-divider">•</span>
      <span> {letInfo?.vreme_poletanja?.split(" ")[1]?.slice(0, 5)}h</span>
    </div>
  </div>

  {error && <p className="error" style={{ color: 'red', fontWeight: 'bold' }}>{error}</p>}

  <form onSubmit={handleSubmit} className="reservation-form">
    <label>Broj karata:</label>
    <input
      type="number"
      value={brojKarata}
      min={1}
      max={slobodnaSedistaOdlazak.length}
      onChange={(e) => setBrojKarata(Number(e.target.value))}
      required
    />

    <div className="price-highlight-card">
      <span className="price-label">Ukupno za uplatu:</span>
      <span className="price-value">{ukupnaCena.toFixed(2)} €</span>
    </div>


          <div className="checkbox-row">
            <input
              type="checkbox"
              id="birajSedista"
              checked={birajSedišta}
              onChange={(e) => setBirajSedišta(e.target.checked)}
            />
            <label htmlFor="birajSedista">
Želim da biram sedišta <small>(doplata od 5€ po sedištu)</small>            </label>
          </div>

         {returnFlights.length > 0 && (
  <div className={`return-flight-card ${selectedReturn ? "active" : ""}`}>
    <div className="return-header">
      <div className="return-title">
        <FaPlaneArrival className="return-icon" />
        <div>
          <span className="return-heading">Povratni let</span>
          <small className="return-subheading">Izaberite datum i vreme povratka</small>
        </div>
      </div>
      {selectedReturn && (
        <span className="return-badge">+{selectedReturn.cena} € / karta</span>
      )}
    </div>

    <div className="custom-select-wrapper">
      <select
        className="return-select"
        value={selectedReturn?.id || ""}
        onChange={(e) => {
          const id = Number(e.target.value);
          const found = returnFlights.find((f) => f.id === id) || null;
          setSelectedReturn(found);
        }}
      >
        <option value="">Bez povratnog leta</option>
        {returnFlights.map((f) => (
          <option key={f.id} value={f.id}>
            {f.polaziste} → {f.odrediste} • {f.vreme_poletanja?.split(" ")[0]} ({f.vreme_poletanja?.split(" ")[1]?.slice(0, 5)}h) — {f.cena} €
          </option>
        ))}
      </select>
    </div>
  </div>
)}
{putnici.map((p, idx) => (
  <div key={idx} className="passenger-card">
    <h4>Putnik {idx + 1}</h4>

    <div className="form-group-icon">
      <FaUser className="input-icon" />
      <input
        type="text"
        placeholder="Ime i prezime putnika"
        value={p.ime}
        onChange={(e) => {
          const next = [...putnici];
          next[idx].ime = e.target.value;
          setPutnici(next);
        }}
        required
      />
    </div>

    <div className="form-group-icon">
      <FaEnvelope className="input-icon" />
      <input
        type="email"
        placeholder="Email adresa"
        value={p.email}
        onChange={(e) => {
          const next = [...putnici];
          next[idx].email = e.target.value;
          setPutnici(next);
        }}
        required
      />
    </div>

    {birajSedišta && (
      <div className="seat-badge-container">
        <p><strong>Sedište (Odlazak):</strong> <span className={p.sedisteOdlazak ? "seat-selected" : "seat-empty"}>{p.sedisteOdlazak || "Nije izabrano"}</span></p>
        {selectedReturn && (
          <p><strong>Sedište (Povratak):</strong> <span className={p.sedistePovratak ? "seat-selected" : "seat-empty"}>{p.sedistePovratak || "Nije izabrano"}</span></p>
        )}
      </div>
    )}
  </div>
))}
          

          {birajSedišta && (
            <div style={{ marginTop: "30px", borderTop: "1px solid #e5e7eb", paddingTop: "20px" }}>
              <h3 style={{ textAlign: "center", color: "#1e40af" }}>Izaberite sedišta za odlazak</h3>
              <p style={{ textAlign: "center", fontSize: "14px", color: "#6b7280" }}>
                Kliknite na mapu aviona da dodelite sedišta putnicima.
              </p>
              
              <SeatMap 
                ukupnoMesta={letInfo?.broj_mesta || 0}
                zauzetaSedista={zauzetaSedistaOdlazak}
                odabranaSedista={putnici.map(p => p.sedisteOdlazak).filter(Boolean)}
                onSeatClick={(seat) => handleSeatToggle(seat, "odlazak")}
              />

              {selectedReturn && (
                <>
                  <h3 style={{ textAlign: "center", color: "#1e40af", marginTop: "40px" }}>Izaberite sedišta za povratak</h3>
                  <SeatMap 
                    ukupnoMesta={selectedReturn?.broj_mesta || 0}
                    zauzetaSedista={zauzetaSedistaPovratak}
                    odabranaSedista={putnici.map(p => p.sedistePovratak).filter(Boolean)}
                    onSeatClick={(seat) => handleSeatToggle(seat, "povratak")}
                  />
                </>
              )}
            </div>
          )}

          <button type="submit" className="reserve-button" disabled={submitting}>
             {submitting ? "Slanje..." : "Rezerviši ✈️"}
           </button>
        </form>
      </div>
    </div>
  );
};

export default Reservation;