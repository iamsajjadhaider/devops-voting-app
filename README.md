🗳️ DevOps Voting Application

This is a minimal, multi-container web application designed to demonstrate fundamental DevOps concepts, including container orchestration using Docker Compose, persistence with volumes, and database interaction between services.

The application allows users to vote on important DevOps pipeline components and displays the real-time results.

🚀 Technologies Used

This project is built using the following components, orchestrated via Docker Compose:

    Web Server: PHP 8.x running on an Apache web server.

    Database: MySQL 8.0 for persistent storage of voting data.

    Orchestration: Docker Compose (version 3.8).

    Frontend Styling: Tailwind CSS (via CDN) for a modern, responsive user interface.

⚙️ How to Set Up and Run

These instructions assume you have Docker and Docker Compose installed on your system.

1. Build and Start the Containers

Navigate to the root directory of the project (devops-voting-app/) and run the following command. The --build flag ensures Docker reads the Dockerfile.web and Dockerfile.db instructions.
Bash

docker compose up --build -d

Component	Port (Host)	Port (Container)
Web App (PHP/Apache)	8080	80
Database (MySQL)	(Internal Only)	3306

2. Access the Application

Once the containers are running, open your web browser and navigate to:

http://localhost:8080

3. Stop and Cleanup

To stop the running containers and remove the data volume (which holds your voting results), use the following command:
Bash

docker compose down -v

📁 Project Structure

The project is divided into separate directories for each service:

devops-voting-app/
├── Dockerfile.web         # Defines the PHP/Apache web service image
├── Dockerfile.db          # Defines the MySQL database service image
├── docker-compose.yml     # Orchestrates the 'web' and 'db' containers
├── web/
│   └── index.php          # PHP frontend logic (connects to 'db', handles voting, displays results)
└── db/
    └── init.sql           # MySQL startup script (creates the 'votes' table and seeds initial data)

🎯 Key DevOps & Database Concepts

1. Service Orchestration

The docker-compose.yml file defines two interconnected services: web and db. The web service uses the service name db as its hostname to connect to the MySQL container, demonstrating internal network communication within the Docker environment.

2. Data Persistence (Volume)

The db service utilizes a named volume (voting_data). This ensures that the MySQL data, including all votes cast, persists even if the db container is stopped or removed.

3. Atomic Voting Update

The PHP application uses an atomic update SQL query to ensure data integrity during voting. This prevents data loss in high-traffic scenarios:
SQL

UPDATE votes SET count = count + 1 WHERE id = ?

This ensures the database handles the increment operation, guaranteeing that every vote is correctly counted without concurrency issues.
