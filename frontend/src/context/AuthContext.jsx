import React, { createContext, useState, useEffect } from "react";
import api from "../api/axios"; 

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const storedUser = localStorage.getItem("user");
    const storedToken = localStorage.getItem("token");

    if (storedUser && storedToken) {
      setUser(JSON.parse(storedUser));
      setToken(storedToken);

      api.defaults.headers.common["Authorization"] = `Bearer ${storedToken}`;
    }
    setLoading(false);
  }, []);

  const login = async (email, password) => {
    try {
      const response = await api.post("/login", { email, password });

      const { user, token } = response.data;

      setUser(user);
      setToken(token);

      localStorage.setItem("user", JSON.stringify(user));
      localStorage.setItem("token", token);

      api.defaults.headers.common["Authorization"] = `Bearer ${token}`;

      return user;
    } catch (error) {
      console.error("Greška pri login-u:", error);
      throw error;
    }
  };





  const logout = () => {
    setUser(null);
    setToken(null);

    localStorage.removeItem("user");
    localStorage.removeItem("token");

    delete api.defaults.headers.common["Authorization"];
  };

  const register = async (data) => {
    try {
      const response = await api.post("/register", data);

      const { user, token } = response.data;

      setUser(user);
      setToken(token);

      localStorage.setItem("user", JSON.stringify(user));
      localStorage.setItem("token", token);

      api.defaults.headers.common["Authorization"] = `Bearer ${token}`;

      return user;
    } catch (error) {
      console.error("Greška pri registraciji:", error);
      throw error;
    }
  };


const loginWithToken = async (newToken) => {
    try {
      api.defaults.headers.common["Authorization"] = `Bearer ${newToken}`;

     const response = await api.get("/me");
     const userData = response.data.user ? response.data.user : response.data;

      setUser(userData);
      setToken(newToken);

      localStorage.setItem("user", JSON.stringify(userData));
      localStorage.setItem("token", newToken);

      return userData;
    } catch (error) {
      console.error("Greška pri povlačenju podataka korisnika nakon Google prijave:", error);
      throw error;
    }
  };

  return (
    <AuthContext.Provider value={{ user, token, loading, login, logout, register, loginWithToken }}>
      {children}
    </AuthContext.Provider>
  );
};
