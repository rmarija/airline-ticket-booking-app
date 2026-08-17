import React from "react";
import Reservation from "./pages/Reservation"
import { BrowserRouter as Router, Routes, Route } from "react-router-dom";
import Success from "./pages/Success";
import Faq from "./pages/Faq";
import Header from "./components/Layout/Header";
import Home from "./pages/Home";
import SearchResults from "./pages/SearchResults";
import FlightDetails from "./pages/FlightDetails";
import { AuthProvider } from "./context/AuthContext";
import Login from "./pages/Login";
import Register from "./pages/Register";
import Contact from "./pages/Contact";
import About from "./pages/About";
import Profile from "./pages/Profile";
import Footer from "./components/Layout/Footer";
import AdminPanel from "./pages/AdminPanel";
import ScrollToTop from "./components/Layout/ScrollToTop";
import GoogleCallback from "./pages/GoogleCallback";
import AiChat from "./components/AiChat/AiChat";

function App() {
  return (
    <AuthProvider>
      <Router>
        <ScrollToTop />
        <Header />

        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/rezultati" element={<SearchResults />} />
          <Route path="/letovi/:id" element={<FlightDetails />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route path="/rezervacija/:id" element={<Reservation />} />
         <Route path="/success" element={<Success />} />
         <Route path="/profil" element={<Profile />} />
         <Route path="/kontakt" element={<Contact />} />
         <Route path="/o-nama" element={<About />} />
         <Route path="/faq" element={<Faq />} />
         <Route path="/admin" element={<AdminPanel />} />
         <Route path="/auth/callback" element={<GoogleCallback />} />
        </Routes>
        
              <AiChat />

          <Footer />
      </Router>
    </AuthProvider>
  );
}

export default App;
