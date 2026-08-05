import React from 'react';
import './SeatMap.css';

const SeatMap = ({ ukupnoMesta, zauzetaSedista, odabranaSedista, onSeatClick }) => {
    const seats = Array.from({ length: ukupnoMesta }, (_, i) => i + 1);

    return (
        <div className="plane-container">
            <div className="plane-nose">
                <span>Pilotska kabina</span>
            </div>
            
            <div className="plane-grid">
                {seats.map((seat) => {
                    const isZauzeto = zauzetaSedista.includes(seat);
                    const isOdabrano = odabranaSedista.includes(seat);
                    const isAisle = seat % 6 === 3; 

                    return (
                        <React.Fragment key={seat}>
                            <button
                                type="button"
                                disabled={isZauzeto}
                                className={`seat-btn ${isZauzeto ? 'zauzeto' : 'slobodno'} ${isOdabrano ? 'odabrano' : ''}`}
                                onClick={() => onSeatClick(seat)}
                            >
                                {seat}
                            </button>
                            
                            {isAisle && <div className="aisle-spacer"></div>}
                        </React.Fragment>
                    );
                })}
            </div>
        </div>
    );
};

export default SeatMap;