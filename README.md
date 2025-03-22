# A&E Wholesale of North Florida

A Laravel-based inventory management and product catalog system for A&E Wholesale of North Florida.

## Features

- **Product Catalog**: Browse the complete inventory with real-time pricing and availability information
- **User Management**: Control access with role-based permissions
- **NetSuite Integration**: Automated inventory synchronization with the NetSuite ERP system
- **Mobile Responsive**: Optimized interface for all device sizes
- **Multi-State Availability**: Filter products based on Florida or Georgia availability

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- SQLite (development) or MySQL (production)

### Installation

1. Clone the repository
```bash
git clone https://github.com/yourusername/ae-wholesale.git
cd ae-wholesale
```

2. Install PHP dependencies
```bash
composer install
```

3. Install NPM packages
```bash
npm install
```

4. Set up environment file
```bash
cp .env.example .env
php artisan key:generate
```

5. Run database migrations
```bash
php artisan migrate --seed
```

6. Start the development server
```bash
php artisan serve
```

### Development Workflow

- **Start the dev environment**: `composer run dev`
- **Run tests**: `php artisan test`
- **Code linting**: `./vendor/bin/pint`
- **Compile assets**: `npm run dev`

## Application Structure

### Key Components

- **User Authentication**: Standard Laravel authentication with role-based permissions
- **Dashboard**: Central hub with access to all system features
- **Admin Dashboard**: Tabbed interface for user, role, and permission management
- **Product Catalog**: Responsive product listing with advanced filtering options
- **NetSuite Integration**: Automated inventory synchronization service

### UI/UX Design

The application follows a consistent design system:
- Standardized typography with `text-xl font-semibold` heading styles
- Consistent padding (p-6) across all containers
- Uniform card styling with hover effects and shadow transitions
- Standardized form controls and interactive elements
- Light-mode pagination across all listing pages

### State and Filtering

Products can be filtered by:
- Brand
- Category
- State availability (Florida, Georgia, or All States)
- Search term (searches across SKU, brand, and description)

## NetSuite Synchronization

The system connects to NetSuite via RESTlets to synchronize inventory data:
- Each item has state availability (Florida, Georgia, or both)
- Pricing information is updated hourly
- Stock levels are maintained in real-time

## License

This project is proprietary software owned by A&E Wholesale of North Florida.