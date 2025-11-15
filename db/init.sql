-- Create the main table for the voting application
-- This script runs automatically when the container starts for the first time.
CREATE TABLE IF NOT EXISTS votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    option_text VARCHAR(255) NOT NULL,
    count INT NOT NULL DEFAULT 0
);

-- Insert the initial poll question and options (seed data)
-- Since the PHP script only queries options, this represents the different vote choices.
INSERT INTO votes (option_text, count) VALUES
('Infrastructure as Code (IaC)', 0),
('Continuous Integration (CI) Automation', 0),
('Monitoring and Logging', 0),
('Automated Testing', 0);

-- Optional: Create a separate table just for the question text (though simple apps often hardcode this)
CREATE TABLE IF NOT EXISTS poll_info (
    question_text VARCHAR(500) NOT NULL
);

INSERT INTO poll_info (question_text) VALUES
('What is the most important component of a great DevOps pipeline?');
