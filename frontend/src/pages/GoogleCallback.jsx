import React, { useEffect, useContext } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { AuthContext } from "../context/AuthContext";

const GoogleCallback = () => {
  const { loginWithToken } = useContext(AuthContext);
  const navigate = useNavigate();
  const location = useLocation();

  useEffect(() => {
    const params = new URLSearchParams(location.search);
    const token = params.get("token");

    if (token) {
      loginWithToken(token)
        .then(() => {
          navigate("/"); 
        })
        .catch(() => {
          navigate("/login"); 
        });
    } else {
      navigate("/login");
    }
  }, [location, loginWithToken, navigate]);

  return (
    <div style={{ display: "flex", justifyContent: "center", marginTop: "100px" }}>
      <h2>Obrada Google prijave...</h2>
    </div>
  );
};

export default GoogleCallback;