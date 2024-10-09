### 1. **Login Credentials**
- **Username**: `admin`
- **Password**: `admin`

### 2. **Setup Instructions**
#### Project Setup Instructions

**Requirements**:
- PHP 7.4 or higher
- MySQL
- Composer
- DDEV (for local development environment)

**Steps to Set Up the Project Locally**:

1. **Unzip and copy the files in your directory**:
   ```bash
   cd your-repo
   ```

2. **Set Up DDEV**:
   - Ensure that DDEV is installed on your machine.
   - Run DDEV to configure and start the environment:
     ```bash
     ddev config
     ddev start
     ```

3. **Install Dependencies**:
   ```bash
   ddev composer install
   ```

4. **Import the Database**:
   - Place the database dump (db.sql) in the project folder.
   - Run the following command to import the database:
     ```bash
     ddev import-db --src=db.sql
     ```

5. **Import Configuration (if necessary)**:
   ```bash
   ddev drush cim -y
   ```

6. **Clear Cache**:
   ```bash
   ddev drush cr
   ```

7. **Access the Site**:
   - After setup, access the local environment.
   - Use the provided credentials to log in.

### 3. **Project details**

#### Project Title: Drupal Project with Custom Features

**Project Description**:
This project is a Drupal-based site built with custom modules and themes. It includes a set of features. The project is developed with DDEV for local development, and it uses Drupal 10.

**Technologies Used**:
- Drupal 10
- DDEV
- SCSS, Gulp for frontend tasks
- Composer for dependency management

**Features**:
- Custom blocks created as per the requirement.
- Custom theming using Bootstrap 5.
- Form validation and submission handling.
- Integration with Font Awesome icons.
