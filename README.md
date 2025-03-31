# Php_MidtermTest
A super basic Php Website for my Midterm test


Overview

This is a PHP-based School Management System that allows you to manage student records, including their personal information, subjects, and scores. The system calculates total scores, averages, and performance classifications for each student.
Features

    Add, edit, and delete student records

    Track subjects and scores for each student

    Automatic calculation of total scores and averages

    Performance classification (Excellent, Very Good, Good, Passed, Failed)

    Search functionality to find students by name

    Responsive design that works on desktop and mobile devices

Requirements

    Web server with PHP support (Apache, Nginx, etc.)

    PHP 7.0 or higher

    Modern web browser

Installation
Option 1: Using GitHub

    Clone the repository:

    git clone https://github.com/JohnmelRojas665/Php_MidtermProject.git

    Move the files to your web server's document root (e.g., htdocs or www folder)

    Access the application through your web browser (e.g., http://localhost/Php_SchoolManagementSystem)

Option 2: Direct Download

    Download the ZIP file from GitHub: https://github.com/JohnmelRojas665/Php_MidtermTest

    Extract the ZIP file to your web server's document root

    Access the application through your web browser

Usage

    Add a Student:

        Fill in the student's name, age, and grade level

        Add subjects and their corresponding scores

        Click "Add Student"

    Edit a Student:

        Click the "Edit" button on a student card

        Modify the student's information

        Click "Update Student"

    Delete a Student:

        Click the "Delete" button on a student card

    Search for Students:

        Use the search bar at the top to find students by name

    Reset All Data:

        Click the "Reset All Students" button to clear all records

File Structure

    index.php - Main application file containing all the logic and HTML

    style.css - Stylesheet for the application

Notes

    All data is stored in PHP sessions and will be cleared when the browser is closed

    For persistent storage, you would need to modify the application to use a database
