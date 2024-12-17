
CREATE TABLE notifications_read (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    sac_id INT NOT NULL,
    read_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id),
    FOREIGN KEY (sac_id) REFERENCES sac(id),
    UNIQUE KEY unique_read (user_id, sac_id)
);