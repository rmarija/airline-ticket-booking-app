import React, { useState, useEffect, useRef } from "react";
import { getCities } from "../../api/flightService";
import "./CityAutocomplete.css";

const CityAutocomplete = ({ label, value, onChange, placeholder }) => {
  const [allCities, setAllCities] = useState([]);
  const [suggestions, setSuggestions] = useState([]);
  const [showDropdown, setShowDropdown] = useState(false);
  const wrapperRef = useRef(null);

  useEffect(() => {
    getCities()
      .then((res) => setAllCities(res.data.data || []))
      .catch((err) => console.error("Greška pri učitavanju gradova:", err));
  }, []);

  useEffect(() => {
    function handleClickOutside(e) {
      if (wrapperRef.current && !wrapperRef.current.contains(e.target)) {
        setShowDropdown(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const handleInputChange = (e) => {
    const inputVal = e.target.value;
    onChange(inputVal);

    if (inputVal.trim() === "") {
      setSuggestions([]);
      setShowDropdown(false);
      return;
    }

    const filtered = allCities.filter((grad) =>
      grad.toLowerCase().startsWith(inputVal.toLowerCase())
    );
    setSuggestions(filtered.slice(0, 6));
    setShowDropdown(filtered.length > 0);
  };

  const handleSelect = (grad) => {
    onChange(grad);
    setShowDropdown(false);
  };

  return (
    <div className="city-autocomplete" ref={wrapperRef}>
      {label && <label className="city-autocomplete-label">{label}</label>}
      <input
        type="text"
        value={value}
        onChange={handleInputChange}
        onFocus={() => value && setShowDropdown(suggestions.length > 0)}
        placeholder={placeholder}
        className="city-autocomplete-input"
        autoComplete="off"
      />
      {showDropdown && (
        <ul className="city-autocomplete-list">
          {suggestions.map((grad) => (
            <li key={grad} onClick={() => handleSelect(grad)}>
              {grad}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

export default CityAutocomplete;