import api from "./axios";


//export const getFlights = (params) => api.get("/letovi", { params });


export const getAllFlights = () => api.get("/letovi");
export const getFlight = (id) => api.get(`/letovi/${id}`);

export const createFlight = (data) => api.post("/letovi", data);
export const updateFlight = (id, data) => api.put(`/letovi/${id}`, data);
export const deleteFlight = (id) => api.delete(`/letovi/${id}`);

export const getReservations = (params) => api.get("/rezervacije", { params });
export const getReservation = (id) => api.get(`/rezervacije/${id}`);
export const createReservation = (data) => api.post("/rezervacije", data);
export const updateReservation = (id, data) => api.put(`/rezervacije/${id}`, data);
export const deleteReservation = (id) => api.delete(`/rezervacije/${id}`);

export const getAvailableSeats = (flightId) => api.get("/slobodna-sedista", { params: { flightId } });
export const cleanupLockedSeats = () => api.delete("/locked-seats/cleanup");

export const getCities = () => api.get("/gradovi");

export default {
  getAllFlights,
  getFlight,
  createFlight,
  updateFlight,
  deleteFlight,
  getReservations,
  getReservation,
  createReservation,
  updateReservation,
  deleteReservation,
  getAvailableSeats,
  cleanupLockedSeats,
  getCities
};