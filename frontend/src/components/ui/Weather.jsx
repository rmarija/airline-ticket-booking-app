import React, { useEffect, useState } from "react";
import axios from "axios";
import "./Weather.css";

const Weather = ({ city = "Beograd" }) => {
  const [weather, setWeather] = useState(null);

  useEffect(() => {
    const fetchWeather = async () => {
      try {
        const response = await axios.get(
          `http://127.0.0.1:8000/api/weather/${city}`
        );
        setWeather(response.data);
      } catch (error) {
        console.error("Greška pri učitavanju vremena:", error);
      }
    };
    fetchWeather();
  }, [city]);

  if (!weather) return <span className="weather-badge">...</span>;

  const getWeatherIcon = (main) => {
    switch (main) {
      case "Clear": return "☀️";
      case "Clouds": return "☁️";
      case "Rain": return "🌧️";
      case "Snow": return "❄️";
      case "Drizzle": return "🌦️";
      case "Thunderstorm": return "⛈️";
      default: return "🌡️";
    }
  };

  return (
  <span className="weather-badge">
    {getWeatherIcon(weather.weather[0].main)}
    <span className="weather-temp">{Math.round(weather.main.temp)}°C</span>
  </span>
);
};

export default Weather;
