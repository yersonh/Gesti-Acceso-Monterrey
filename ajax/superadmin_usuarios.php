<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SuperAdminModel.php';
require_once __DIR__ . '/../controllers/SuperAdminController.php';

(new SuperAdminController())->api();