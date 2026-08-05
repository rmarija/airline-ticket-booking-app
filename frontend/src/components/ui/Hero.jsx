import React from "react";
import "./Hero.css";

const Hero = () => {
  return (
    <section className="hero">
      <div className="hero-overlay">
        <h1 className="text-4xl md:text-5xl font-bold mb-4">
          Kupite avio karte brzo i jednostavno
        </h1>
        <p className="text-lg mb-6">
          Rezervišite vase letove i uživajte u putovanju!
        </p>
       
   <a href="#search-form" className="hero-button">
          Pretraži letove
        </a>
      </div>
    </section>
  );
};

export default Hero;


