<?php
require 'server/db_connection.php'; // your PDO connection

try {
    // Optional: load a specific UUID or all applications
    $uuid = isset($_GET['uuid']) ? $_GET['uuid'] : null;

    if ($uuid) {
        $stmt = $pdo->prepare("SELECT * FROM applications WHERE uuid = ?");
        $stmt->execute([$uuid]);
    } else {
        $stmt = $pdo->query("SELECT * FROM applications ORDER BY created_at DESC");
    }

    $applications = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $appData = isset($row['application_data']) ? json_decode($row['application_data'], true) : [];

        if ($appData) {
            $applications[] = [
                "id" => $row['id'],
                "uuid" => $row['uuid'] ?? $appData['uuid'] ?? 'N/A',
                "fullName" => $appData['givenName'] . ' ' . $appData['surName'],
                "givenName" => $appData['givenName'] ?? '',
                "surName" => $appData['surName'] ?? '',
                "email" => $appData['email'] ?? '',
                "mobile" => $appData['mobile'] ?? '',
                "nationality" => $appData['nationality'] ?? '',
                "passportNo" => $appData['passportNo'] ?? '',
                "visaType" => $appData['visaType'] ?? '',
                "country" => $appData['country'] ?? 'Thailand',
                "profession" => $appData['profession'] ?? '',
                "jobTitle" => $appData['jobTitle'] ?? '',
                "employerName" => $appData['employerName'] ?? '',
                "travelStart" => $appData['travelStart'] ?? '',
                "travelEnd" => $appData['travelEnd'] ?? '',
                "departureCity" => $appData['departureCity'] ?? '',
                "destinationCity" => $appData['destinationCity'] ?? '',
                "hotelName" => $appData['hotelName'] ?? '',
                "hotelAddress" => $appData['hotelAddress'] ?? '',
                "airlinePNR" => $appData['airlinePNR'] ?? '',
                "galileoPNR" => $appData['galileoPNR'] ?? '',
                "participants" => $appData['participants'] ?? [],
                "airTicketPassengers" => $appData['airTicketPassengers'] ?? [],
                "flightItineraries" => $appData['flightItineraries'] ?? [],
                "travelHistoryList" => $appData['travelHistoryList'] ?? '',
                "visitedCountryBefore" => $appData['visitedCountryBefore'] ?? false,
                "lastCountryVisit" => $appData['lastCountryVisit'] ?? '',
                "previousVisaNo" => $appData['previousVisaNo'] ?? '',
                "status" => $row['status'] ?? 'completed',
                "coverLetter" => $row['cover_letter'] ?? '',
                "wordDocument" => $row['word_document'] ?? '',
                "createdAt" => $row['created_at'] ?? date('Y-m-d H:i:s'),
                "updatedAt" => $row['updated_at'] ?? date('Y-m-d H:i:s'),
                "rawData" => $appData,
                "source" => "database"
            ];
        }
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thai Visa Applications Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .application-card {
            transition: all 0.3s ease;
            border-left: 5px solid transparent;
        }

        .application-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .progress-bar {
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .status-draft {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            box-shadow: 0 2px 5px rgba(107, 114, 128, 0.2);
        }

        .status-generated {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            box-shadow: 0 2px 5px rgba(59, 130, 246, 0.2);
        }

        .status-completed {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 2px 5px rgba(16, 185, 129, 0.2);
        }

        .status-exported {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            box-shadow: 0 2px 5px rgba(139, 92, 246, 0.2);
        }

        .country-flag {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .thailand-flag {
            background: linear-gradient(135deg, #ed1c24 0%, #00247d 100%);
        }

        .malaysia-flag {
            background: linear-gradient(135deg, #cc0001 0%, #010066 50%, #ffffff 50%, #cc0001 100%);
        }

        .singapore-flag {
            background: linear-gradient(135deg, #ed1c24 0%, #ffffff 50%, #ffffff 50%, #ed1c24 100%);
        }

        .vietnam-flag {
            background: linear-gradient(135deg, #da251d 0%, #ff0 100%);
        }

        .indonesia-flag {
            background: linear-gradient(to bottom, #ff0000 50%, #ffffff 50%);
        }

        .visa-type-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .tourist-visa {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        .business-visa {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            color: #0c4a6e;
            border: 1px solid #7dd3fc;
        }

        .medical-visa {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #166534;
            border: 1px solid #86efac;
        }

        .student-visa {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .profession-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.6rem;
            border-radius: 15px;
            font-weight: 500;
            background: #f3f4f6;
            color: #4b5563;
        }

        .participant-chip {
            display: inline-flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 0.25rem 0.75rem;
            margin: 0.15rem;
            font-size: 0.75rem;
        }

        .action-btn {
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <header class="text-center mb-12">
            <div class="flex flex-col items-center mb-8">
                <div class="relative mb-6">
                    <div class="w-24 h-24 bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 rounded-full flex items-center justify-center mb-4 shadow-xl">
                        <i class="fas fa-file-contract text-white text-4xl"></i>
                    </div>
                    <div class="absolute -bottom-2 -right-2 w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-plane text-white text-lg"></i>
                    </div>
                </div>
                <h1 class="text-4xl font-bold text-gray-800 mb-3 bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">Thai Visa Applications</h1>
                <p class="text-gray-600 max-w-2xl text-lg">Generate professional visa application letters for Thailand. Manage all your saved applications in one place.</p>
            </div>
        </header>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border border-gray-100">
                <div class="flex items-center">
                    <div class="w-14 h-14 bg-gradient-to-r from-blue-100 to-blue-200 rounded-xl flex items-center justify-center mr-5">
                        <i class="fas fa-file-contract text-blue-600 text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Total Applications</p>
                        <h3 id="total-applications" class="text-3xl font-bold text-gray-800 mt-1">0</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border border-gray-100">
                <div class="flex items-center">
                    <div class="w-14 h-14 bg-gradient-to-r from-green-100 to-green-200 rounded-xl flex items-center justify-center mr-5">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Completed</p>
                        <h3 id="completed-applications" class="text-3xl font-bold text-gray-800 mt-1">0</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border border-gray-100">
                <div class="flex items-center">
                    <div class="w-14 h-14 bg-gradient-to-r from-amber-100 to-amber-200 rounded-xl flex items-center justify-center mr-5">
                        <i class="fas fa-clock text-amber-600 text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">In Progress</p>
                        <h3 id="inprogress-applications" class="text-3xl font-bold text-gray-800 mt-1">0</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border border-gray-100">
                <div class="flex items-center">
                    <div class="w-14 h-14 bg-gradient-to-r from-purple-100 to-purple-200 rounded-xl flex items-center justify-center mr-5">
                        <i class="fas fa-file-word text-purple-600 text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Exported</p>
                        <h3 id="exported-applications" class="text-3xl font-bold text-gray-800 mt-1">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications Dashboard -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-200 mb-16">
            <div class="px-8 py-7 border-b border-gray-200 bg-gradient-to-r from-blue-50/80 to-gray-50/80 backdrop-blur-sm">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center">
                    <div class="mb-6 lg:mb-0">
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Saved Applications</h2>
                        <p class="text-gray-600">Your Thai visa applications from database and local storage</p>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <button id="refresh-btn" class="bg-white hover:bg-gray-50 text-gray-800 font-medium py-3 px-5 rounded-xl transition duration-300 flex items-center shadow-sm border border-gray-200 action-btn">
                            <i class="fas fa-sync-alt mr-3"></i> Refresh
                        </button>
                        <button id="new-application" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium py-3 px-6 rounded-xl transition duration-300 flex items-center shadow-lg action-btn">
                            <i class="fas fa-plus mr-3"></i> New Application
                        </button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mt-8 flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                        <select id="filter-country" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Countries</option>
                            <option value="Thailand">Thailand</option>
                            <option value="Malaysia">Malaysia</option>
                            <option value="Singapore">Singapore</option>
                            <option value="Vietnam">Vietnam</option>
                            <option value="Indonesia">Indonesia</option>
                        </select>
                    </div>

                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Visa Type</label>
                        <select id="filter-visa" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Visa Types</option>
                            <option value="Tourist Visa">Tourist Visa</option>
                            <option value="Business Visa">Business Visa</option>
                            <option value="Medical Visa">Medical Visa</option>
                            <option value="Student Visa">Student Visa</option>
                        </select>
                    </div>

                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="filter-status" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="generated">Generated</option>
                            <option value="completed">Completed</option>
                            <option value="exported">Exported</option>
                        </select>
                    </div>

                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Profession</label>
                        <select id="filter-profession" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Professions</option>
                            <option value="Employee">Employee</option>
                            <option value="Business">Business Owner</option>
                            <option value="Doctor">Doctor</option>
                            <option value="Lawyer">Lawyer</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="p-6 lg:p-8">
                <div id="applications-list" class="space-y-8">
                    <!-- Applications will be listed here -->
                </div>

                <div id="no-applications" class="text-center py-20 hidden">
                    <div class="max-w-lg mx-auto">
                        <div class="w-40 h-40 mx-auto mb-8 relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-100 to-gray-100 rounded-full blur-xl opacity-50"></div>
                            <div class="relative w-full h-full bg-gradient-to-br from-blue-50 to-gray-50 rounded-full flex items-center justify-center border-2 border-dashed border-gray-300">
                                <i class="fas fa-file-alt text-gray-400 text-6xl"></i>
                            </div>
                        </div>
                        <h3 class="text-2xl font-semibold text-gray-600 mb-3">No applications found</h3>
                        <p class="text-gray-500 mb-8 max-w-md mx-auto">Start by creating your first Thai visa application. Fill in the details and generate professional cover letters instantly.</p>
                        <button id="create-first-app" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium py-4 px-10 rounded-xl transition duration-300 shadow-lg action-btn text-lg">
                            <i class="fas fa-plus mr-3"></i> Create New Application
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-20 pt-10 border-t border-gray-200">
            <div class="flex flex-col lg:flex-row justify-between items-center">
                <div class="mb-8 lg:mb-0">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                            <i class="fas fa-globe-asia text-white text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-lg">TravHub Global Limited</h4>
                            <p class="text-sm text-gray-500">Professional Visa Application Services</p>
                        </div>
                    </div>
                </div>
                <div class="text-center lg:text-right">
                    <p class="text-gray-500 text-sm mb-2">© 2025 TravHub Global Limited. All rights reserved.</p>
                    <p class="text-gray-400 text-xs">This dashboard is for managing Thai visa application letters.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Application Details Modal -->
    <div id="application-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center p-4 z-50 hidden backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-hidden">
            <div class="p-7 border-b border-gray-200 flex justify-between items-center bg-gradient-to-r from-blue-50 to-gray-50">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl flex items-center justify-center mr-5 shadow-md">
                        <i class="fas fa-file-contract text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">Application Details</h3>
                        <p class="text-gray-600 text-sm mt-1">Complete information about this visa application</p>
                    </div>
                </div>
                <button id="close-modal" class="text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 w-12 h-12 rounded-full flex items-center justify-center transition duration-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-7 overflow-y-auto max-h-[60vh]">
                <div id="modal-content">
                    <!-- Modal content will be loaded here -->
                </div>
            </div>
            <div class="p-7 border-t border-gray-200 bg-gray-50 flex flex-col sm:flex-row justify-between items-center">
                <div class="text-sm text-gray-500 mb-4 sm:mb-0">
                    <i class="far fa-clock mr-2"></i>
                    <span id="modal-timestamp">Loading...</span>
                </div>
                <div class="flex space-x-4">
                    <button id="view-letter-btn" class="bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium py-3 px-6 rounded-xl transition duration-300 border border-blue-200 action-btn hidden">
                        <i class="fas fa-eye mr-2"></i> View Letter
                    </button>
                    <button id="cancel-modal" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-6 rounded-xl transition duration-300">
                        Close
                    </button>
                    <button id="continue-application" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium py-3 px-8 rounded-xl transition duration-300 shadow-md">
                        <i class="fas fa-edit mr-2"></i> Continue Application
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Application data structure
        let applications = <?php echo json_encode($applications, JSON_PRETTY_PRINT); ?>;
        let currentModalUUID = '';

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadApplications();
            setupEventListeners();
        });

        // Set up event listeners
        function setupEventListeners() {
            document.getElementById('refresh-btn').addEventListener('click', loadApplications);
            document.getElementById('new-application').addEventListener('click', createNewApplication);
            document.getElementById('create-first-app').addEventListener('click', createNewApplication);
            document.getElementById('close-modal').addEventListener('click', closeModal);
            document.getElementById('cancel-modal').addEventListener('click', closeModal);
            document.getElementById('continue-application').addEventListener('click', continueApplication);
            document.getElementById('view-letter-btn').addEventListener('click', viewCoverLetter);

            // Filter event listeners
            ['filter-country', 'filter-visa', 'filter-status', 'filter-profession'].forEach(id => {
                document.getElementById(id).addEventListener('change', filterApplications);
            });

            // Close modal when clicking outside
            document.getElementById('application-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
        }

        // Load all applications
        function loadApplications() {
            // First load from database via PHP
            let dbApplications = <?php echo json_encode($applications, JSON_PRETTY_PRINT); ?>;

            // Then load from localStorage
            let localStorageApplications = [];
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key.startsWith("visaForm_")) {
                    try {
                        const appData = JSON.parse(localStorage.getItem(key));
                        if (appData && appData.uuid) {
                            const app = convertLocalStorageToApp(appData);
                            if (app) {
                                localStorageApplications.push(app);
                            }
                        }
                    } catch (e) {
                        console.error("Error parsing localStorage:", key, e);
                    }
                }
            }

            // Merge applications
            applications = [...dbApplications, ...localStorageApplications];

            // Remove duplicates (based on UUID)
            const uniqueUUIDs = new Set();
            applications = applications.filter(app => {
                if (!app.uuid || uniqueUUIDs.has(app.uuid)) {
                    return false;
                }
                uniqueUUIDs.add(app.uuid);
                return true;
            });

            // Sort by latest timestamp
            applications.sort((a, b) => {
                const dateA = new Date(a.updatedAt || a.createdAt || 0);
                const dateB = new Date(b.updatedAt || b.createdAt || 0);
                return dateB - dateA;
            });

            renderApplications();
            updateStats();
        }

        // Convert localStorage data to application format
        function convertLocalStorageToApp(appData) {
            if (!appData || !appData.uuid) return null;

            // Calculate status based on data
            let status = 'draft';
            if (appData.visaType && appData.givenName && appData.surName && appData.passportNo) {
                status = 'generated';
            }

            // Check if cover letter was generated
            const hasCoverLetter = appData.coverLetterGenerated || false;
            if (hasCoverLetter) {
                status = 'completed';
            }

            // Check if exported to Word
            const hasWordExport = appData.wordExported || false;
            if (hasWordExport) {
                status = 'exported';
            }

            return {
                id: null,
                uuid: appData.uuid,
                fullName: (appData.givenName || '') + ' ' + (appData.surName || ''),
                givenName: appData.givenName || '',
                surName: appData.surName || '',
                email: appData.email || '',
                mobile: appData.mobile || '',
                nationality: appData.nationality || '',
                passportNo: appData.passportNo || '',
                visaType: appData.visaType || '',
                country: appData.country || 'Thailand',
                profession: appData.profession || '',
                jobTitle: appData.jobTitle || '',
                employerName: appData.employerName || '',
                travelStart: appData.travelStart || '',
                travelEnd: appData.travelEnd || '',
                departureCity: appData.departureCity || '',
                destinationCity: appData.destinationCity || '',
                hotelName: appData.hotelName || '',
                hotelAddress: appData.hotelAddress || '',
                airlinePNR: appData.airlinePNR || '',
                galileoPNR: appData.galileoPNR || '',
                participants: appData.participants || [],
                airTicketPassengers: appData.airTicketPassengers || [],
                flightItineraries: appData.flightItineraries || [],
                travelHistoryList: appData.travelHistoryList || '',
                visitedCountryBefore: appData.visitedCountryBefore || false,
                lastCountryVisit: appData.lastCountryVisit || '',
                previousVisaNo: appData.previousVisaNo || '',
                status: status,
                createdAt: appData.createdAt || new Date().toISOString(),
                updatedAt: appData.updatedAt || new Date().toISOString(),
                source: 'local',
                rawData: appData
            };
        }

        // Render the applications list
        function renderApplications(filteredApps = null) {
            const appsToRender = filteredApps || applications;
            const listContainer = document.getElementById('applications-list');
            const noApplications = document.getElementById('no-applications');

            if (appsToRender.length === 0) {
                listContainer.innerHTML = '';
                noApplications.classList.remove('hidden');
                return;
            }

            noApplications.classList.add('hidden');

            let html = '';
            appsToRender.forEach((app, index) => {
                const createdDate = new Date(app.createdAt || Date.now());
                const formattedDate = createdDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });

                const timeAgo = getTimeAgo(createdDate);
                const participantsCount = app.participants ? app.participants.length : 0;
                const totalApplicants = participantsCount + 1;

                // Determine status
                let statusClass, statusText;
                switch (app.status) {
                    case 'completed':
                        statusClass = 'status-completed';
                        statusText = 'Completed';
                        break;
                    case 'exported':
                        statusClass = 'status-exported';
                        statusText = 'Exported';
                        break;
                    case 'generated':
                        statusClass = 'status-generated';
                        statusText = 'Generated';
                        break;
                    default:
                        statusClass = 'status-draft';
                        statusText = 'Draft';
                }

                // Visa type class
                let visaTypeClass = '';
                switch (app.visaType) {
                    case 'Tourist Visa':
                        visaTypeClass = 'tourist-visa';
                        break;
                    case 'Business Visa':
                        visaTypeClass = 'business-visa';
                        break;
                    case 'Medical Visa':
                        visaTypeClass = 'medical-visa';
                        break;
                    case 'Student Visa':
                        visaTypeClass = 'student-visa';
                        break;
                }

                // Country flag
                let countryFlagClass = 'thailand-flag';
                switch (app.country) {
                    case 'Malaysia':
                        countryFlagClass = 'malaysia-flag';
                        break;
                    case 'Singapore':
                        countryFlagClass = 'singapore-flag';
                        break;
                    case 'Vietnam':
                        countryFlagClass = 'vietnam-flag';
                        break;
                    case 'Indonesia':
                        countryFlagClass = 'indonesia-flag';
                        break;
                }

                // Country code
                const countryCode = app.country ? app.country.substring(0, 2).toUpperCase() : 'TH';

                // Calculate progress
                const progress = calculateApplicationProgress(app);

                // Extract PNR from airlinePNR
                let pnrDisplay = '';
                if (app.airlinePNR) {
                    const pnrMatch = app.airlinePNR.match(/(\w{6})/);
                    pnrDisplay = pnrMatch ? pnrMatch[1] : '';
                }

                html += `
                    <div class="application-card bg-white border border-gray-200 rounded-2xl p-7 hover:border-blue-300 fade-in">
                        <div class="flex flex-col xl:flex-row justify-between gap-8">
                            <!-- Left Column -->
                            <div class="flex-1">
                                <!-- Header Section -->
                                <div class="flex flex-col lg:flex-row lg:items-start justify-between mb-6">
                                    <div class="mb-4 lg:mb-0">
                                        <div class="flex items-center mb-3">
                                            <div class="country-flag ${countryFlagClass} mr-4" title="${app.country || 'Thailand'}">
                                                ${countryCode}
                                            </div>
                                            <h3 class="font-bold text-gray-800 text-xl">
                                                ${app.fullName || 'New Application'}
                                            </h3>
                                            ${app.source === 'local' ? 
                                                '<span class="ml-3 text-xs px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full font-medium">Local</span>' : 
                                                '<span class="ml-3 text-xs px-3 py-1 bg-green-100 text-green-800 rounded-full font-medium">Database</span>'
                                            }
                                        </div>
                                        
                                        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600 mb-4">
                                            <span class="flex items-center bg-gray-100 px-3 py-1.5 rounded-full">
                                                <i class="fas fa-passport mr-2"></i> ${app.passportNo || 'No passport'}
                                            </span>
                                            ${app.visaType ? `
                                                <span class="visa-type-badge ${visaTypeClass}">
                                                    ${app.visaType}
                                                </span>
                                            ` : ''}
                                            ${app.profession ? `
                                                <span class="profession-badge">
                                                    <i class="fas fa-briefcase mr-1"></i> ${app.profession}
                                                </span>
                                            ` : ''}
                                            <span class="flex items-center">
                                                <i class="fas fa-users mr-2"></i> ${totalApplicants} applicant${totalApplicants > 1 ? 's' : ''}
                                            </span>
                                            <span class="flex items-center text-gray-500">
                                                <i class="far fa-clock mr-1"></i> ${timeAgo}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start space-x-3">
                                        ${pnrDisplay ? `
                                            <div class="text-center">
                                                <div class="text-xs text-gray-500 mb-1">PNR</div>
                                                <div class="font-mono text-sm font-bold text-gray-800 bg-gray-100 px-3 py-1.5 rounded-lg">${pnrDisplay}</div>
                                            </div>
                                        ` : ''}
                                        <span class="status-badge ${statusClass}">${statusText}</span>
                                    </div>
                                </div>
                                
                                <!-- Application ID -->
                                <div class="mb-6">
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-fingerprint text-gray-400 mr-3"></i>
                                        <span class="text-gray-500 mr-2">Application ID:</span>
                                        <code class="font-mono text-gray-800 bg-gray-100 px-3 py-1.5 rounded-lg text-xs border border-gray-200 break-all">${app.uuid}</code>
                                    </div>
                                </div>
                                
                                <!-- Progress Section -->
                                <div class="mb-6">
                                    <div class="flex justify-between text-sm text-gray-700 font-medium mb-2">
                                        <span>Completion Progress</span>
                                        <span>${progress}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="h-3 rounded-full progress-bar ${progress === 100 ? 'bg-gradient-to-r from-green-500 to-green-600' : 'bg-gradient-to-r from-blue-500 to-blue-600'}" 
                                             style="width: ${progress}%"></div>
                                    </div>
                                </div>
                                
                                <!-- Travel Information -->
                                ${(app.travelStart && app.travelEnd) ? `
                                    <div class="bg-gradient-to-r from-blue-50 to-gray-50 p-4 rounded-xl border border-blue-100 mb-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-plane-departure text-blue-500 text-xl mr-4"></i>
                                            <div class="flex-1">
                                                <div class="flex flex-wrap items-center justify-between">
                                                    <div class="mb-2 sm:mb-0">
                                                        <span class="text-sm text-gray-500">Travel Dates:</span>
                                                        <span class="text-gray-800 font-medium ml-2">${formatDate(app.travelStart)} - ${formatDate(app.travelEnd)}</span>
                                                    </div>
                                                    <div class="text-sm text-gray-600">
                                                        ${app.departureCity || ''} → ${app.destinationCity || 'Thailand'}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ` : ''}
                                
                                <!-- Participants -->
                                ${participantsCount > 0 ? `
                                    <div class="mt-4">
                                        <div class="text-sm text-gray-500 mb-2">Travel Companions:</div>
                                        <div class="flex flex-wrap gap-2">
                                            ${app.participants.slice(0, 3).map(p => `
                                                <div class="participant-chip">
                                                    <i class="fas fa-user mr-2 text-gray-400"></i>
                                                    <span class="font-medium">${p.givenName || ''} ${p.surName || ''}</span>
                                                    <span class="text-gray-500 text-xs ml-2">(${p.relationship || 'Family'})</span>
                                                </div>
                                            `).join('')}
                                            ${participantsCount > 3 ? `
                                                <div class="participant-chip bg-blue-50 border-blue-100">
                                                    <span class="text-blue-600 font-medium">+${participantsCount - 3} more</span>
                                                </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="xl:w-64 flex flex-col space-y-3">
                                <button class="view-app-btn bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-medium py-3.5 px-5 rounded-xl transition duration-300 flex items-center justify-center action-btn"
                                        onclick="showApplicationDetails('${app.uuid}')" 
                                        title="View Details">
                                    <i class="fas fa-eye mr-3"></i>
                                    <span>View Details</span>
                                </button>
                                <button class="continue-app-btn bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-medium py-3.5 px-5 rounded-xl transition duration-300 flex items-center justify-center action-btn shadow-md"
                                        onclick="continueApplicationDirect('${app.uuid}')" 
                                        title="Continue Application">
                                    <i class="fas fa-edit mr-3"></i>
                                    <span>Continue</span>
                                </button>
                                ${app.coverLetter ? `
                                    <button class="view-letter-btn bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-medium py-3.5 px-5 rounded-xl transition duration-300 flex items-center justify-center action-btn"
                                            onclick="viewCoverLetterDirect('${app.uuid}')" 
                                            title="View Cover Letter">
                                        <i class="fas fa-file-alt mr-3"></i>
                                        <span>View Letter</span>
                                    </button>
                                ` : ''}
                                <button class="delete-app-btn bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-medium py-3.5 px-5 rounded-xl transition duration-300 flex items-center justify-center action-btn"
                                        onclick="deleteApplication('${app.uuid}')" 
                                        title="Delete Application">
                                    <i class="fas fa-trash mr-3"></i>
                                    <span>Delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            listContainer.innerHTML = html;
        }

        // Filter applications
        function filterApplications() {
            const countryFilter = document.getElementById('filter-country').value;
            const visaFilter = document.getElementById('filter-visa').value;
            const statusFilter = document.getElementById('filter-status').value;
            const professionFilter = document.getElementById('filter-profession').value;

            let filteredApps = applications;

            if (countryFilter) {
                filteredApps = filteredApps.filter(app => app.country === countryFilter);
            }

            if (visaFilter) {
                filteredApps = filteredApps.filter(app => app.visaType === visaFilter);
            }

            if (statusFilter) {
                filteredApps = filteredApps.filter(app => app.status === statusFilter);
            }

            if (professionFilter) {
                filteredApps = filteredApps.filter(app => app.profession === professionFilter);
            }

            renderApplications(filteredApps);
        }

        // Calculate application progress
        function calculateApplicationProgress(app) {
            if (app.status === 'completed' || app.status === 'exported') return 100;

            let completedFields = 0;
            let totalFields = 0;

            // Personal Information (5 fields)
            const personalFields = ['givenName', 'surName', 'nationality', 'passportNo', 'email'];
            personalFields.forEach(field => {
                totalFields++;
                if (app[field] && app[field].toString().trim()) completedFields++;
            });

            // Visa Details (5 fields)
            const visaFields = ['visaType', 'travelStart', 'travelEnd', 'departureCity', 'destinationCity'];
            visaFields.forEach(field => {
                totalFields++;
                if (app[field] && app[field].toString().trim()) completedFields++;
            });

            // Profession Details (2-3 fields)
            if (app.profession) {
                completedFields++;
                totalFields++;
                if (app.profession === 'Employee' && app.jobTitle && app.employerName) {
                    completedFields += 2;
                    totalFields += 2;
                }
            } else {
                totalFields++;
            }

            // Accommodation (2 fields)
            if (app.hotelName && app.hotelName.trim()) completedFields++;
            if (app.hotelAddress && app.hotelAddress.trim()) completedFields++;
            totalFields += 2;

            return totalFields > 0 ? Math.round((completedFields / totalFields) * 100) : 0;
        }

        // Format date
        function formatDate(dateString) {
            if (!dateString) return 'Not set';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }

        // Get time ago
        function getTimeAgo(date) {
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 60) {
                return `${diffMins} minute${diffMins !== 1 ? 's' : ''} ago`;
            } else if (diffHours < 24) {
                return `${diffHours} hour${diffHours !== 1 ? 's' : ''} ago`;
            } else if (diffDays < 7) {
                return `${diffDays} day${diffDays !== 1 ? 's' : ''} ago`;
            } else {
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });
            }
        }

        // Update dashboard statistics
        function updateStats() {
            const totalApplications = applications.length;
            const completedApplications = applications.filter(app => app.status === 'completed').length;
            const generatedApplications = applications.filter(app => app.status === 'generated').length;
            const exportedApplications = applications.filter(app => app.status === 'exported').length;
            const inProgressApplications = totalApplications - completedApplications - exportedApplications - generatedApplications;

            document.getElementById('total-applications').textContent = totalApplications;
            document.getElementById('completed-applications').textContent = completedApplications;
            document.getElementById('inprogress-applications').textContent = inProgressApplications;
            document.getElementById('exported-applications').textContent = exportedApplications;
        }

        // Show application details in modal
        function showApplicationDetails(uuid) {
            const application = applications.find(app => app.uuid === uuid);
            if (!application) {
                alert('Application not found!');
                return;
            }

            currentModalUUID = uuid;
            const modalContent = document.getElementById('modal-content');
            const viewLetterBtn = document.getElementById('view-letter-btn');

            // Show/hide view letter button
            if (application.coverLetter) {
                viewLetterBtn.classList.remove('hidden');
            } else {
                viewLetterBtn.classList.add('hidden');
            }

            const createdDate = new Date(application.createdAt || Date.now());
            const updatedDate = new Date(application.updatedAt || application.createdAt || Date.now());

            document.getElementById('modal-timestamp').textContent =
                `Created: ${createdDate.toLocaleDateString()} | Updated: ${updatedDate.toLocaleDateString()}`;

            // Get profession details
            let professionDetails = '';
            if (application.profession === 'Employee') {
                professionDetails = `${application.jobTitle || ''} at ${application.employerName || ''}`;
            } else if (application.profession === 'Business') {
                professionDetails = `${application.rawData?.businessRole || ''} - ${application.rawData?.businessName || ''}`;
            } else if (application.profession === 'Doctor') {
                professionDetails = `${application.rawData?.doctorPosition || ''} at ${application.rawData?.hospitalName || ''}`;
            } else if (application.profession === 'Lawyer') {
                professionDetails = `Advocate at ${application.rawData?.lawFirmName || ''}`;
            } else if (application.profession === 'Other') {
                professionDetails = application.rawData?.professionOther || '';
            }

            let html = `
                <div class="space-y-8">
                    <!-- Header -->
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                        <div class="flex-1">
                            <h4 class="text-2xl font-bold text-gray-800 mb-2">${application.fullName}</h4>
                            <div class="flex flex-wrap items-center gap-3 mb-4">
                                <span class="text-gray-600">
                                    <i class="fas fa-passport mr-1"></i> ${application.passportNo || 'No passport'}
                                </span>
                                <span class="text-gray-600">
                                    <i class="fas fa-flag mr-1"></i> ${application.nationality || ''}
                                </span>
                                <span class="text-gray-600">
                                    <i class="fas fa-envelope mr-1"></i> ${application.email || ''}
                                </span>
                                <span class="text-gray-600">
                                    <i class="fas fa-phone mr-1"></i> ${application.mobile || ''}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-center">
                                <div class="text-sm text-gray-500 mb-1">Application ID</div>
                                <div class="font-mono text-sm font-bold text-gray-800 bg-gray-100 px-4 py-2 rounded-xl">${application.uuid.substring(0, 20)}...</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Application ID Full -->
                    <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                        <div class="flex items-center">
                            <i class="fas fa-fingerprint text-gray-400 text-xl mr-4"></i>
                            <div class="flex-1">
                                <p class="text-sm text-gray-500 mb-1">Full Application ID</p>
                                <p class="font-mono text-gray-800 break-all">${application.uuid}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Flight Information -->
                    ${application.flightItineraries && application.flightItineraries.length > 0 ? `
                        <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 p-5 rounded-xl border border-cyan-200">
                            <h5 class="font-bold text-gray-700 mb-4 text-lg flex items-center">
                                <i class="fas fa-plane-departure mr-3 text-cyan-500"></i> Flight Information
                            </h5>
                            <div class="space-y-4">
                                ${application.flightItineraries.map((flight, index) => `
                                    <div class="bg-white p-4 rounded-lg border border-cyan-100">
                                        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-3">
                                            <div class="mb-3 lg:mb-0">
                                                <p class="font-medium text-gray-800">${flight.airline || 'Unknown Airline'}</p>
                                                <p class="text-sm text-gray-500">${flight.from || ''} → ${flight.to || ''}</p>
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <span class="px-3 py-1 bg-cyan-100 text-cyan-800 rounded-full text-sm font-medium">
                                                    ${flight.class || 'Economy'}
                                                </span>
                                                <span class="px-3 py-1 ${flight.status === 'Confirmed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'} rounded-full text-sm font-medium">
                                                    ${flight.status || 'Pending'}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                            <div class="flex items-center">
                                                <i class="fas fa-plane-departure mr-3 text-gray-400"></i>
                                                <div>
                                                    <p class="text-gray-500">Departure</p>
                                                    <p class="text-gray-800 font-medium">${formatDateTime(flight.depart)}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-plane-arrival mr-3 text-gray-400"></i>
                                                <div>
                                                    <p class="text-gray-500">Arrival</p>
                                                    <p class="text-gray-800 font-medium">${formatDateTime(flight.arrive)}</p>
                                                </div>
                                            </div>
                                            <div class="flex justify-end items-center">
                                                <a class="view-app-btn bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-medium py-3.5 px-5 rounded-xl transition duration-300 flex items-center justify-center action-btn"
                                                        href="download_ticket.php?pnr=${application.uuid}" 
                                                        title="Download Ticket">
                                                    <i class="fa-solid fa-download mr-3"></i>
                                                    <span>Download Ticket</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                    
                    <!-- Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Visa Information -->
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-5 rounded-xl border border-blue-200">
                            <h5 class="font-bold text-gray-700 mb-4 text-lg flex items-center">
                                <i class="fas fa-plane mr-3 text-blue-500"></i> Travel Information
                            </h5>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Visa Type</p>
                                    <p class="text-gray-800 font-medium">${application.visaType || 'Not specified'}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Destination Country</p>
                                    <p class="text-gray-800 font-medium flex items-center">
                                        <span class="country-flag ${application.country === 'Thailand' ? 'thailand-flag' : ''} mr-3">
                                            ${application.country ? application.country.substring(0, 2).toUpperCase() : 'TH'}
                                        </span>
                                        ${application.country || 'Thailand'}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Travel Dates</p>
                                    <p class="text-gray-800">${formatDate(application.travelStart)} - ${formatDate(application.travelEnd)}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Route</p>
                                    <p class="text-gray-800">${application.departureCity || 'Not specified'} → ${application.destinationCity || 'Thailand'}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Professional Information -->
                        <div class="bg-gradient-to-br from-green-50 to-green-100 p-5 rounded-xl border border-green-200">
                            <h5 class="font-bold text-gray-700 mb-4 text-lg flex items-center">
                                <i class="fas fa-briefcase mr-3 text-green-500"></i> Professional Information
                            </h5>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Profession</p>
                                    <p class="text-gray-800 font-medium">${application.profession || 'Not specified'}</p>
                                </div>
                                ${professionDetails ? `
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Position/Company</p>
                                        <p class="text-gray-800">${professionDetails}</p>
                                    </div>
                                ` : ''}
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Application Status</p>
                                    <p class="text-gray-800 font-medium">${application.status === 'completed' ? 'Completed' : 
                                                                       application.status === 'generated' ? 'Generated' : 
                                                                       application.status === 'exported' ? 'Exported to Word' : 'Draft'}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1">Progress</p>
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2 mr-3">
                                            <div class="h-2 rounded-full ${calculateApplicationProgress(application) === 100 ? 'bg-green-500' : 'bg-blue-500'}" 
                                                 style="width: ${calculateApplicationProgress(application)}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">${calculateApplicationProgress(application)}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Accommodation -->
                    ${application.hotelName || application.hotelAddress ? `
                        <div class="bg-gradient-to-br from-amber-50 to-amber-100 p-5 rounded-xl border border-amber-200">
                            <h5 class="font-bold text-gray-700 mb-4 text-lg flex items-center">
                                <i class="fas fa-hotel mr-3 text-amber-500"></i> Accommodation
                            </h5>
                            <div class="space-y-4">
                                ${application.hotelName ? `
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Hotel Name</p>
                                        <p class="text-gray-800 font-medium">${application.hotelName}</p>
                                    </div>
                                ` : ''}
                                ${application.hotelAddress ? `
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Hotel Address</p>
                                        <p class="text-gray-800">${application.hotelAddress}</p>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    ` : ''}
                    
                    <!-- Travel Companions -->
                    ${application.participants && application.participants.length > 0 ? `
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-5 rounded-xl border border-purple-200">
                            <h5 class="font-bold text-gray-700 mb-4 text-lg flex items-center">
                                <i class="fas fa-users mr-3 text-purple-500"></i> Travel Companions
                                <span class="ml-auto text-sm font-normal text-gray-500">${application.participants.length} person${application.participants.length !== 1 ? 's' : ''}</span>
                            </h5>
                            <div class="space-y-4">
                                ${application.participants.map((participant, index) => `
                                    <div class="bg-white p-4 rounded-lg border border-purple-100">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between">
                                            <div class="mb-3 sm:mb-0">
                                                <div class="flex items-center mb-2">
                                                    <div class="w-10 h-10 bg-gradient-to-r from-purple-100 to-purple-200 rounded-full flex items-center justify-center mr-3">
                                                        <i class="fas fa-user text-purple-600"></i>
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-gray-800">${participant.givenName || ''} ${participant.surName || ''}</p>
                                                        <p class="text-sm text-gray-500">${participant.relationship || 'Family member'}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-sm text-gray-600 space-y-1">
                                                ${participant.passportNo ? `
                                                    <div class="flex items-center">
                                                        <i class="fas fa-passport mr-2 text-gray-400"></i>
                                                        <span>${participant.passportNo}</span>
                                                    </div>
                                                ` : ''}
                                                ${participant.dob ? `
                                                    <div class="flex items-center">
                                                        <i class="fas fa-birthday-cake mr-2 text-gray-400"></i>
                                                        <span>${formatDate(participant.dob)}</span>
                                                    </div>
                                                ` : ''}
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;

            modalContent.innerHTML = html;
            document.getElementById('application-modal').classList.remove('hidden');
        }

        // Format date and time
        function formatDateTime(dateTimeString) {
            if (!dateTimeString) return 'Not specified';
            const date = new Date(dateTimeString);
            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Close the modal
        function closeModal() {
            document.getElementById('application-modal').classList.add('hidden');
            currentModalUUID = '';
        }

        // View cover letter
        function viewCoverLetter() {
            if (currentModalUUID) {
                viewCoverLetterDirect(currentModalUUID);
            }
        }

        // View cover letter directly
        function viewCoverLetterDirect(uuid) {
            const application = applications.find(app => app.uuid === uuid);
            if (application && application.coverLetter) {
                // Open cover letter in new window
                const newWindow = window.open();
                newWindow.document.write(application.coverLetter);
                newWindow.document.close();
            } else {
                alert('Cover letter not available for this application.');
            }
        }

        // Continue application from modal
        function continueApplication() {
            if (currentModalUUID) {
                continueApplicationDirect(currentModalUUID);
            }
        }

        // Continue application directly
        function continueApplicationDirect(uuid) {
            console.log('Redirecting to application:', uuid);

            // Check if application exists in localStorage
            const appData = localStorage.getItem(`visaForm_${uuid}`);
            if (appData) {
                // Redirect to Thai visa generator with UUID parameter
                window.location.href = `application.php?uuid=${encodeURIComponent(uuid)}`;
            } else {
                // Check in database applications
                const app = applications.find(a => a.uuid === uuid && a.source !== 'local');
                if (app) {
                    // Redirect to view page or edit page
                    window.location.href = `edit-application.php?uuid=${encodeURIComponent(uuid)}`;
                } else {
                    alert('Application data not found. Creating new application...');
                    createNewApplication();
                }
            }
        }

        // Create a new application
        function createNewApplication() {
            // Redirect to Thai visa generator without parameters for new application
            window.location.href = 'application.php';
        }

        // Delete an application
        function deleteApplication(uuid) {
            if (!confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
                return;
            }

            // Remove from localStorage
            localStorage.removeItem(`visaForm_${uuid}`);

            // Remove from applications array
            applications = applications.filter(app => app.uuid !== uuid);

            // Show success message
            alert(`Application ${uuid} has been deleted.`);

            // Reload the applications list
            renderApplications();
            updateStats();
        }
        
    </script>
</body>

</html>