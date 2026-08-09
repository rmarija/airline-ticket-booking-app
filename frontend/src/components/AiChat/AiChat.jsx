import React, { useState, useRef, useEffect } from 'react';
import './AiChat.css';
import axios from '../../api/axios';

const AiChat = () => {
    const [isOpen, setIsOpen] = useState(false);
    const [messages, setMessages] = useState([
        { sender: 'ai', text: 'Zdravo! Ja sam tvoj pametni asistent. Koji let tražiš danas?' }
    ]);
    const [input, setInput] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const chatEndRef = useRef(null);

    useEffect(() => {
        chatEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    const formatMessage = (text) => {
        if (!text) return { __html: '' };
        
        const htmlText = text.replace(
            /\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, 
            '<a href="$2" target="_blank" rel="noopener noreferrer" style="color: #0056b3; font-weight: 600; text-decoration: underline;">$1</a>'
        );
        
        return { __html: htmlText };
    };

   const sendMessage = async () => {
        if (!input.trim()) return;

        const userText = input;
        const updatedMessages = [...messages, { sender: 'user', text: userText }];
        
        setMessages(updatedMessages);
        setInput('');
        setIsLoading(true);

        try {
            const response = await axios.post('/chat', { 
                message: userText,
                history: messages 
            });
            
            setMessages((prev) => [...prev, { sender: 'ai', text: response.data.reply }]);
        } catch (error) {
            console.error('Greška:', error);
            setMessages((prev) => [...prev, { sender: 'ai', text: 'Izvini, trenutno imam tehničkih problema sa povezivanjem.' }]);
        } finally {
            setIsLoading(false);
        }
    };

    const handleKeyPress = (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    };

    return (
        <div className="ai-chat-container">
            <button className="chat-toggle-btn" onClick={() => setIsOpen(!isOpen)}>
                {isOpen ? '✕ Zatvori' : '💬 AI Asistent'}
            </button>

            {isOpen && (
                <div className="chat-window">
                    <div className="chat-header">
                        <h3>Agent za letove</h3>
                    </div>
                    
                    <div className="chat-body">
                        {messages.map((msg, index) => (
                            <div 
                                key={index} 
                                className={`message-bubble ${msg.sender}`}
                                dangerouslySetInnerHTML={formatMessage(msg.text)}
                            />
                        ))}
                        {isLoading && (
                            <div className="message-bubble ai typing">
                                Kuca...
                            </div>
                        )}
                        <div ref={chatEndRef} />
                    </div>

                    <div className="chat-footer">
                        <input 
                            type="text" 
                            placeholder="Npr. najjeftiniji let do.." 
                            value={input}
                            onChange={(e) => setInput(e.target.value)}
                            onKeyPress={handleKeyPress}
                            disabled={isLoading}
                        />
                        <button onClick={sendMessage} disabled={isLoading || !input.trim()}>
                            ➤
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
};

export default AiChat;