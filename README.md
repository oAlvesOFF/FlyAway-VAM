<p align="center">
  <img src="/logo_ico.png" alt="FlyAway-VAM Logo" width="40"/>
</p>

<h1 align="center">✈️ FlyAway-VAM</h1>

<p align="center">
  <strong>Professional Virtual Airline Management Platform | Plataforma Profissional de Gestão de Companhia Aérea Virtual</strong>
</p>

<p align="center">
  <a href="#english">🇬🇧 English</a> &nbsp;|&nbsp;
  <a href="#português">🇧🇷 Português</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12"/>
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+"/>
  <img src="https://img.shields.io/badge/Livewire-3.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire 3"/>
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS"/>
  <img src="https://img.shields.io/badge/Tauri-2.x-FFC131?style=for-the-badge&logo=tauri&logoColor=white" alt="Tauri 2"/>
  <img src="https://img.shields.io/badge/Vite-7.x-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite 7"/>
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="MIT License"/>
</p>

---

## English

**FlyAway-VAM** is a full-featured, professional-grade **Virtual Airline Management (VAM)** platform built with the Laravel ecosystem. It provides everything a virtual airline community needs — from pilot onboarding and flight tracking to fleet management, PIREP submission, live maps, and a powerful admin panel — all in a single, cohesive system.

It ships with an integrated **ACARS desktop client** built with Tauri + React, enabling real-time flight data transmission directly from the simulator to the platform.

---

### 🗂️ Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Architecture Overview](#-architecture-overview)
- [Project Structure](#-project-structure)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Running the Application](#-running-the-application)
- [ACARS Client](#-acars-client)
- [API Documentation](#-api-documentation)
- [License](#-license)

---

### 🚀 Features

#### 👨‍✈️ Pilot & User Management
- Pilot registration, onboarding, and profile pages
- Rank system with automatic promotion based on flight hours
- Role-based access control (RBAC) with granular permissions
- Pilot roster and public profile pages
- Pilot statistics dashboard (hours, flights, landings, etc.)
- Account suspension with reason tracking
- Avatar / profile picture support
- API key generation per pilot

#### ✈️ Flight Operations
- **ACARS Integration** — real-time flight data via MQTT protocol
- **Live Map** — real-time aircraft positions via Leaflet.js
- **PIREP System** — full pilot report filing, review, and approval workflow
- **Flight History** — complete log of all completed flights per pilot
- **Active Flights** — real-time monitoring of flights in progress
- **Simbrief Integration** — automatic flight plan import from SimBrief

#### 📅 Scheduling & Bookings
- Comprehensive flight schedule management
- Pilot bid & booking system (`My Bookings`)
- Schedule creation, editing, and assignment

#### 🛩️ Fleet Management
- Aircraft database with type, registration, and status tracking
- Fleet assignment and availability control

#### 🏆 Gamification & Engagement
- **Achievements System** — award badges and achievements to pilots
- **Leaderboard** — competitive rankings by flight hours, flights, and more
- **Tours** — structured tour campaigns for pilots to complete

#### 📰 CMS & Content
- **News** module — post airline announcements
- **Custom Pages** — fully editable CMS pages (e.g., About, Rules)
- **Handbook** — in-platform pilot handbook viewer
- **Airports Database** — searchable global airport directory

#### 🛠️ Administration
- Comprehensive **Admin Dashboard** with KPIs and statistics
- **Staff Management** — assign and manage staff roles
- **Settings Panel** — configure airline name, ICAO, country, and global preferences
- **Activity Log** — full audit trail of admin actions
- **PIREP Review** — approve, reject, or request revision on pilot reports
- **Notification System** — alert pilots of important events

---

### 🧰 Tech Stack

| Layer | Technology |
|---|---|
| **Backend Framework** | Laravel 12 (PHP 8.2+) |
| **Reactive UI** | Livewire 3 + Volt |
| **Frontend Styling** | Tailwind CSS 3 |
| **Asset Bundling** | Vite 7 |
| **Authentication** | Laravel Breeze |
| **Real-time / MQTT** | php-mqtt/client |
| **API Documentation** | L5-Swagger (OpenAPI 3) |
| **Database (default)** | SQLite / MySQL |
| **Queue & Cache** | Database driver |
| **Desktop ACARS Client** | Tauri 2 + React 19 + TypeScript |
| **Maps** | Leaflet.js + react-leaflet |
| **Testing** | PHPUnit |

---

### 🏗️ Architecture Overview

```
FlyAway-VAM/
├── Web Platform (Laravel)      ← Main CMS + Admin + Pilot Portal
│   ├── REST API                ← OpenAPI documented endpoints
│   ├── Livewire Components     ← Reactive UI without full JS framework
│   └── MQTT Listener           ← Receives real-time ACARS telemetry
│
└── ACARS Client (Tauri)        ← Cross-platform desktop app
    ├── React 19 + TypeScript   ← UI layer
    ├── Leaflet Map             ← In-app live map
    └── Tauri Store             ← Local persistent settings
```

---

### 📁 Project Structure

```
FlyAway-VAM/
├── acars-client/               # Tauri desktop ACARS client (React + TS)
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Admin, API, Auth, Welcome controllers
│   │   └── Middleware/         # Custom middleware (roles, API keys, etc.)
│   ├── Livewire/               # Livewire component classes
│   ├── Models/                 # Eloquent models
│   │   ├── User.php            # Pilot / User model
│   │   ├── Pirep.php           # Pilot Reports
│   │   ├── Aircraft.php        # Fleet aircraft
│   │   ├── Schedule.php        # Flight schedules
│   │   ├── ActiveFlight.php    # Real-time flight tracking
│   │   ├── Achievement.php     # Achievements / badges
│   │   ├── Tour.php            # Tour campaigns
│   │   ├── Rank.php            # Pilot ranks
│   │   ├── Role.php            # RBAC roles
│   │   ├── Permission.php      # Granular permissions
│   │   └── ...                 # Airport, News, Settings, etc.
│   ├── Services/
│   │   ├── MqttService.php     # MQTT real-time telemetry
│   │   ├── SimbriefService.php # SimBrief API integration
│   │   └── SettingsService.php # Global settings helper
│   └── OpenApi/                # OpenAPI annotations
├── database/
│   ├── migrations/             # All database migrations
│   └── seeders/                # Database seeders
├── resources/
│   ├── views/
│   │   ├── welcome.blade.php   # Public landing page
│   │   ├── dashboard.blade.php # Pilot dashboard
│   │   ├── livewire/           # All Livewire blade views
│   │   │   ├── admin/          # Admin panel views
│   │   │   ├── live-map.blade.php
│   │   │   ├── flights.blade.php
│   │   │   ├── pilot-stats.blade.php
│   │   │   ├── leaderboard.blade.php
│   │   │   ├── achievements.blade.php
│   │   │   ├── tours.blade.php
│   │   │   ├── simbrief.blade.php
│   │   │   └── ...
│   │   └── components/         # Reusable blade components
│   ├── css/                    # Global stylesheets
│   └── js/                     # JavaScript entry points
├── routes/                     # Web + API route definitions
├── public/                     # Publicly served assets
├── storage/                    # Logs, API docs, framework cache
├── .env.example                # Environment configuration template
├── composer.json               # PHP dependencies
├── package.json                # Node dependencies
├── tailwind.config.js          # Tailwind CSS configuration
├── vite.config.js              # Vite build configuration
├── flyaway_vam.sql             # Database dump (optional starter)
└── install.php                 # Web-based installer
```

---

### 📋 Requirements

| Requirement | Version |
|---|---|
| PHP | ≥ 8.2 |
| Composer | ≥ 2.x |
| Node.js | ≥ 20.x |
| NPM | ≥ 10.x |
| Database | SQLite 3 **or** MySQL 8+ |
| (Optional) MQTT Broker | Mosquitto or any MQTT 3.1.1+ broker |
| (Optional) Rust + Cargo | For building the ACARS client |

---

### ⚙️ Installation

#### 1. Clone the Repository
```bash
git clone https://github.com/your-username/FlyAway-VAM.git
cd FlyAway-VAM
```

#### 2. Install PHP Dependencies
```bash
composer install
```

#### 3. Install Node Dependencies
```bash
npm install
```

#### 4. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```
Edit `.env` with your database credentials and application settings.

#### 5. Run Migrations
```bash
php artisan migrate
```

> **Optional:** Import the included `flyaway_vam.sql` dump for a pre-seeded database.

#### 6. Build Frontend Assets
```bash
npm run build
```

#### 7. Set Storage Permissions *(Linux/macOS only)*
```bash
chmod -R 775 storage bootstrap/cache
```

#### 8. Use the Web Installer *(Alternative)*
Navigate to `http://your-domain/install.php` for the guided web-based installer.

---

### 🔧 Configuration

Edit your `.env` file with the following key settings:

```env
# Application
APP_NAME="FlyAway VAM"
APP_URL=http://localhost

# Database (SQLite default, or switch to MySQL)
DB_CONNECTION=sqlite
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=flyaway_vam
# DB_USERNAME=root
# DB_PASSWORD=secret

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_FROM_ADDRESS="noreply@yourairline.com"

# MQTT (for ACARS real-time tracking)
# Add your broker credentials here if applicable
```

---

### ▶️ Running the Application

#### Development Mode (all services concurrently)
```bash
composer run dev
```
This starts simultaneously:
- `php artisan serve` — Laravel web server
- `php artisan queue:listen` — Background job queue
- `php artisan pail` — Real-time log viewer
- `npm run dev` — Vite HMR dev server

#### Production Mode
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan serve
```

---

### 🖥️ ACARS Client

The `acars-client/` directory contains a standalone **cross-platform desktop application** built with:
- **Tauri 2** — Native desktop shell (Rust-based)
- **React 19** + **TypeScript** — UI layer
- **Leaflet.js** — Embedded live flight map
- **Tauri Store Plugin** — Persistent local settings

#### Build & Run ACARS Client
```bash
cd acars-client
npm install
npm run tauri dev       # Development
npm run tauri build     # Production binary
```

> Requires **Rust + Cargo** to be installed. See [tauri.app/start](https://tauri.app/start/) for setup instructions.

---

### 📖 API Documentation

FlyAway-VAM includes a full **OpenAPI 3 / Swagger** documented REST API.

After installation, generate the API docs:
```bash
php artisan l5-swagger:generate
```

Then navigate to:
```
http://your-domain/api/documentation
```

All endpoints support **API Key** authentication — each pilot is assigned a unique API key for external tool integration (e.g., ACARS clients, third-party apps).

---

### 🧪 Running Tests

```bash
composer run test
# or
php artisan test
```

---

### 📄 License

This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

---

<p align="center">
  Built with ❤️ for the virtual aviation community
</p>

---
---

## Português

**FlyAway-VAM** é uma plataforma completa e profissional de **Gerenciamento de Companhia Aérea Virtual (VAM)**, desenvolvida com o ecossistema Laravel. Ela fornece tudo o que uma comunidade de aviação virtual precisa — desde o cadastro de pilotos e rastreamento de voos até gerenciamento de frota, submissão de PIREPs, mapa ao vivo e um painel administrativo poderoso — tudo em um único sistema coeso.

A plataforma vem acompanhada de um **cliente ACARS desktop** integrado, desenvolvido com Tauri + React, que permite a transmissão de dados de voo em tempo real diretamente do simulador para a plataforma.

---

### 🗂️ Índice

- [Funcionalidades](#-funcionalidades)
- [Stack Tecnológica](#-stack-tecnológica)
- [Visão Geral da Arquitetura](#-visão-geral-da-arquitetura)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Requisitos](#-requisitos)
- [Instalação](#-instalação)
- [Configuração](#-configuração)
- [Executando a Aplicação](#-executando-a-aplicação)
- [Cliente ACARS](#-cliente-acars)
- [Documentação da API](#-documentação-da-api)
- [Licença](#-licença)

---

### 🚀 Funcionalidades

#### 👨‍✈️ Gerenciamento de Pilotos e Usuários
- Registro, integração e páginas de perfil de pilotos
- Sistema de patentes com promoção automática por horas de voo
- Controle de acesso baseado em funções (RBAC) com permissões granulares
- Roster de pilotos e páginas de perfil público
- Painel de estatísticas do piloto (horas, voos, pousos, etc.)
- Suspensão de conta com registro de motivo
- Suporte a avatar / foto de perfil
- Geração de chave de API por piloto

#### ✈️ Operações de Voo
- **Integração ACARS** — dados de voo em tempo real via protocolo MQTT
- **Mapa Ao Vivo** — posições de aeronaves em tempo real via Leaflet.js
- **Sistema PIREP** — submissão, revisão e aprovação completa de relatórios de voo
- **Histórico de Voos** — registro completo de todos os voos concluídos por piloto
- **Voos Ativos** — monitoramento em tempo real dos voos em andamento
- **Integração SimBrief** — importação automática de plano de voo do SimBrief

#### 📅 Escalas e Reservas
- Gerenciamento completo de escalas de voo
- Sistema de bid e reserva de voos (`Minhas Reservas`)
- Criação, edição e atribuição de escalas

#### 🛩️ Gerenciamento de Frota
- Banco de dados de aeronaves com tipo, matrícula e controle de status
- Atribuição e controle de disponibilidade da frota

#### 🏆 Gamificação e Engajamento
- **Sistema de Conquistas** — conceda badges e conquistas aos pilotos
- **Placar de Líderes** — rankings competitivos por horas, voos e mais
- **Tours** — campanhas de tours estruturadas para pilotos completarem

#### 📰 CMS e Conteúdo
- **Módulo de Notícias** — publique anúncios da companhia aérea
- **Páginas Personalizadas** — páginas CMS totalmente editáveis (ex: Sobre, Regras)
- **Handbook** — visualizador de manual do piloto na plataforma
- **Banco de Aeroportos** — diretório global pesquisável de aeroportos

#### 🛠️ Administração
- **Painel Admin** completo com KPIs e estatísticas
- **Gerenciamento de Staff** — atribuir e gerenciar funções de equipe
- **Painel de Configurações** — configurar nome, ICAO, país e preferências globais da companhia
- **Log de Atividades** — trilha de auditoria completa das ações administrativas
- **Revisão de PIREPs** — aprovar, rejeitar ou solicitar revisão de relatórios de pilotos
- **Sistema de Notificações** — alertar pilotos sobre eventos importantes

---

### 🧰 Stack Tecnológica

| Camada | Tecnologia |
|---|---|
| **Framework Backend** | Laravel 12 (PHP 8.2+) |
| **UI Reativa** | Livewire 3 + Volt |
| **Estilização Frontend** | Tailwind CSS 3 |
| **Empacotamento de Assets** | Vite 7 |
| **Autenticação** | Laravel Breeze |
| **Tempo Real / MQTT** | php-mqtt/client |
| **Documentação da API** | L5-Swagger (OpenAPI 3) |
| **Banco de Dados (padrão)** | SQLite / MySQL |
| **Fila e Cache** | Driver de banco de dados |
| **Cliente ACARS Desktop** | Tauri 2 + React 19 + TypeScript |
| **Mapas** | Leaflet.js + react-leaflet |
| **Testes** | PHPUnit |

---

### 🏗️ Visão Geral da Arquitetura

```
FlyAway-VAM/
├── Plataforma Web (Laravel)    ← CMS Principal + Admin + Portal do Piloto
│   ├── API REST                ← Endpoints documentados com OpenAPI
│   ├── Componentes Livewire    ← UI reativa sem framework JS completo
│   └── Listener MQTT           ← Recebe telemetria ACARS em tempo real
│
└── Cliente ACARS (Tauri)       ← App desktop multiplataforma
    ├── React 19 + TypeScript   ← Camada de UI
    ├── Mapa Leaflet            ← Mapa ao vivo integrado
    └── Tauri Store             ← Configurações locais persistentes
```

---

### 📁 Estrutura do Projeto

```
FlyAway-VAM/
├── acars-client/               # Cliente ACARS desktop Tauri (React + TS)
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Controllers: Admin, API, Auth, Welcome
│   │   └── Middleware/         # Middlewares customizados (roles, chaves de API)
│   ├── Livewire/               # Classes de componentes Livewire
│   ├── Models/                 # Models Eloquent
│   │   ├── User.php            # Model de Piloto / Usuário
│   │   ├── Pirep.php           # Relatórios de Voo (PIREPs)
│   │   ├── Aircraft.php        # Aeronaves da Frota
│   │   ├── Schedule.php        # Escalas de Voo
│   │   ├── ActiveFlight.php    # Rastreamento em tempo real
│   │   ├── Achievement.php     # Conquistas / badges
│   │   ├── Tour.php            # Campanhas de Tours
│   │   ├── Rank.php            # Patentes dos Pilotos
│   │   ├── Role.php            # Funções RBAC
│   │   ├── Permission.php      # Permissões granulares
│   │   └── ...                 # Aeroporto, Notícias, Configurações, etc.
│   ├── Services/
│   │   ├── MqttService.php     # Telemetria MQTT em tempo real
│   │   ├── SimbriefService.php # Integração com a API do SimBrief
│   │   └── SettingsService.php # Helper de configurações globais
│   └── OpenApi/                # Anotações OpenAPI
├── database/
│   ├── migrations/             # Todas as migrations do banco de dados
│   └── seeders/                # Seeders do banco de dados
├── resources/
│   ├── views/
│   │   ├── welcome.blade.php   # Página pública de entrada
│   │   ├── dashboard.blade.php # Painel do piloto
│   │   ├── livewire/           # Todas as views Livewire
│   │   │   ├── admin/          # Views do painel administrativo
│   │   │   ├── live-map.blade.php
│   │   │   ├── flights.blade.php
│   │   │   ├── pilot-stats.blade.php
│   │   │   ├── leaderboard.blade.php
│   │   │   ├── achievements.blade.php
│   │   │   ├── tours.blade.php
│   │   │   ├── simbrief.blade.php
│   │   │   └── ...
│   │   └── components/         # Componentes blade reutilizáveis
│   ├── css/                    # Folhas de estilo globais
│   └── js/                     # Entry points JavaScript
├── routes/                     # Definições de rotas Web + API
├── public/                     # Assets servidos publicamente
├── storage/                    # Logs, docs da API, cache do framework
├── .env.example                # Modelo de configuração de ambiente
├── composer.json               # Dependências PHP
├── package.json                # Dependências Node
├── tailwind.config.js          # Configuração do Tailwind CSS
├── vite.config.js              # Configuração de build do Vite
├── flyaway_vam.sql             # Dump do banco de dados (starter opcional)
└── install.php                 # Instalador via navegador
```

---

### 📋 Requisitos

| Requisito | Versão |
|---|---|
| PHP | ≥ 8.2 |
| Composer | ≥ 2.x |
| Node.js | ≥ 20.x |
| NPM | ≥ 10.x |
| Banco de Dados | SQLite 3 **ou** MySQL 8+ |
| (Opcional) Broker MQTT | Mosquitto ou qualquer broker MQTT 3.1.1+ |
| (Opcional) Rust + Cargo | Para compilar o cliente ACARS |

---

### ⚙️ Instalação

#### 1. Clonar o Repositório
```bash
git clone https://github.com/seu-usuario/FlyAway-VAM.git
cd FlyAway-VAM
```

#### 2. Instalar Dependências PHP
```bash
composer install
```

#### 3. Instalar Dependências Node
```bash
npm install
```

#### 4. Configurar o Ambiente
```bash
cp .env.example .env
php artisan key:generate
```
Edite o arquivo `.env` com as credenciais do banco de dados e configurações da aplicação.

#### 5. Executar as Migrations
```bash
php artisan migrate
```

> **Opcional:** Importe o dump `flyaway_vam.sql` incluído para um banco de dados pré-configurado.

#### 6. Compilar os Assets do Frontend
```bash
npm run build
```

#### 7. Definir Permissões de Storage *(apenas Linux/macOS)*
```bash
chmod -R 775 storage bootstrap/cache
```

#### 8. Usar o Instalador Web *(Alternativa)*
Acesse `http://seu-dominio/install.php` para o instalador guiado via navegador.

---

### 🔧 Configuração

Edite o arquivo `.env` com as seguintes configurações principais:

```env
# Aplicação
APP_NAME="FlyAway VAM"
APP_URL=http://localhost

# Banco de Dados (SQLite padrão, ou troque para MySQL)
DB_CONNECTION=sqlite
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=flyaway_vam
# DB_USERNAME=root
# DB_PASSWORD=secret

# E-mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_FROM_ADDRESS="noreply@suacompanhia.com"

# MQTT (para rastreamento em tempo real via ACARS)
# Adicione as credenciais do seu broker aqui, se aplicável
```

---

### ▶️ Executando a Aplicação

#### Modo de Desenvolvimento (todos os serviços simultâneos)
```bash
composer run dev
```
Inicia simultaneamente:
- `php artisan serve` — Servidor web Laravel
- `php artisan queue:listen` — Fila de jobs em background
- `php artisan pail` — Visualizador de logs em tempo real
- `npm run dev` — Servidor Vite com HMR

#### Modo de Produção
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan serve
```

---

### 🖥️ Cliente ACARS

O diretório `acars-client/` contém um **aplicativo desktop multiplataforma** independente, desenvolvido com:
- **Tauri 2** — Shell nativo de desktop (baseado em Rust)
- **React 19** + **TypeScript** — Camada de UI
- **Leaflet.js** — Mapa de voo ao vivo integrado
- **Plugin Tauri Store** — Configurações locais persistentes

#### Compilar e Executar o Cliente ACARS
```bash
cd acars-client
npm install
npm run tauri dev       # Desenvolvimento
npm run tauri build     # Binário de produção
```

> Requer **Rust + Cargo** instalados. Consulte [tauri.app/start](https://tauri.app/start/) para instruções de configuração.

---

### 📖 Documentação da API

FlyAway-VAM inclui uma API REST completamente documentada com **OpenAPI 3 / Swagger**.

Após a instalação, gere a documentação da API:
```bash
php artisan l5-swagger:generate
```

Em seguida, acesse:
```
http://seu-dominio/api/documentation
```

Todos os endpoints suportam autenticação por **Chave de API** — cada piloto recebe uma chave de API única para integração com ferramentas externas (ex: clientes ACARS, apps de terceiros).

---

### 🧪 Executando os Testes

```bash
composer run test
# ou
php artisan test
```

---

### 📄 Licença

Este projeto está licenciado sob a **Licença MIT**. Consulte o arquivo [LICENSE](LICENSE) para mais detalhes.

---

<p align="center">
  Desenvolvido com ❤️ para a comunidade de aviação virtual
</p>
