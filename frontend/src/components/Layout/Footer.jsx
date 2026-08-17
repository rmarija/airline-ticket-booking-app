import React from "react";
import { Link } from "react-router-dom";
import "./Footer.css";

const Footer = () => {
  return (
    <footer className="footer">
      <div className="footer-container">
        <div className="footer-left">
          <h2>Veloro</h2>
          <p>
            Kupite avionske karte brzo i jednostavno. <br />
            Rezervišite povratne letove i uživajte u putovanju.
          </p>
        </div>

        <div className="footer-center">
          <ul>
            <li><Link to="/">Početna</Link></li>
            <li><Link to="/o-nama">O nama</Link></li>
            <li><Link to="/kontakt">Kontakt</Link></li>
            <li><Link to="/faq">FAQ</Link></li>
          </ul>
        </div>

        <div className="footer-right">
          <a href="https://facebook.com" target="_blank" rel="noreferrer">Facebook</a>
          <a href="https://instagram.com" target="_blank" rel="noreferrer">Instagram</a>
         
        </div>
      </div>

      <div className="footer-bottom">
© {new Date().getFullYear()} Veloro. Sva prava zadržana.      </div>
    </footer>
  );
};

export default Footer;

