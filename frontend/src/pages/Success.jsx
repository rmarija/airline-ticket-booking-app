import React from "react";
import { Link } from "react-router-dom";
import "./Success.css";

const Success = () => {
  return (
    <div className="success-container">
      <div className="success-content">
        <div className="success-card">
          <h1>Rezervacija uspešna!</h1>
          <p>Vaša karta je uspešno rezervisana.</p>
          <p>
             Detalji o letu, izabranim sedištima, kao i{" "}
  <strong>elektronska karta u PDF formatu</strong>{" "}
  poslati su na Vaš email.
          </p>
          <p className="text-sm">
            Molimo Vas da proverite email (uključujući spam folder).
          </p>
         <Link to="/" className="success-back-button">
  Nazad na početnu
</Link>
        </div>
      </div>
    </div>
  );
};

export default Success;
