import React from "react";
import "./Hero.css";

const Hero = () => {
  return (
    <section className="hero">
      <div className="hero-overlay">
        <h1 className="hero-title">
  Vaša sledeća avantura počinje <span className="hero-highlight">jednim klikom</span>
</h1>
<p className="text-lg mb-6">
Brza pretraga i jednostavna rezervacija letova u nekoliko sekundi.</p>
<a href="#search-form" className="hero-button">
Započni pretragu</a>
      </div>
    </section>
  );
};

export default Hero;


