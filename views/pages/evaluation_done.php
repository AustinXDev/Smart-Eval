    <div class="wrapper w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden card-animate" style="max-width: 400px; margin: 0 auto;">
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-8 text-center relative overflow-hidden">
                <div class="relative z-10">
                    <div class="text-4xl mb-2 flex justify-center "> <img src="./../../public/assets/images/aite-logo.png" alt="aite-logo" loading="lazy" class="w-15 h-15"> </div>
                    <h1 class="text-3xl font-bold mb-2">Evaluation Receipt</h1>
                    <p class="text-sm opacity-90">Successfully Submitted</p>
                    <div class="mt-4 inline-block bg-white bg-opacity-20 px-3 py-1 rounded-full text-xs backdrop-blur">
                        <span id="period-name">Loading...</span>
                    </div>
                </div>
            </div>
 
            <!-- Content -->
            <div class="p-6">
                <!-- Loading State -->
                <div id="loading" class="text-center py-12">
                    <div class="inline-block">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
                    </div>
                    <p class="text-gray-500 mt-4">Loading your evaluation results...</p>
                </div>
 
                <!-- Data Loaded State -->
                <div id="content" style="display: none;">
                    <!-- Stats -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-700 text-white rounded-lg p-6 mb-6 text-center">
                        <div class="text-4xl font-bold mb-1">
                            <span id="total-count">0</span>
                        </div>
                        <div class="text-sm opacity-90">
                            Teacher<span id="plural-s">s</span> Evaluated
                        </div>
                    </div>
 
                    <!-- Empty State -->
                    <div id="empty-state" style="display: none;" class="text-center py-12">
                        <div class="text-6xl mb-4">📋</div>
                        <p class="text-2xl font-semibold text-gray-800 mb-2">No Evaluations Yet</p>
                        <p class="text-gray-600 mb-6">You haven't evaluated any teachers in the current period.</p>
                        <div class="flex gap-4 justify-center flex-wrap">
                            <button onclick="window.location.href='/Smart-Eval/views/student/evaluation.view.php'" 
                                    class="btn-gradient text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition">
                                Start Evaluation
                            </button>
                        </div>
                    </div>
 
                    <!-- Teachers List -->
                    <div id="teachers-list" style="display: none;">
                        <div class="space-y-2 mb-6 border-b pb-4">
                            <!-- Teachers will be inserted here -->
                        </div>
 
                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-wrap">
                            <button onclick="window.print()" 
                                    class="flex-1 btn-gradient text-white px-4 py-2 rounded-lg font-semibold text-sm hover:shadow-lg transition">
                                Print Receipt
                            </button>
                            <a href="../../app/auth/logout.php" 
                                    class="flex items-center justify-center gap-2 
                                    flex-1 border border-gray-300 
                                    text-gray-700 px-4 py-2.5 rounded-lg 
                                    font-semibold text-sm 
                                    hover:bg-gray-100 
                                    transition duration-200">
                                Go Back
                            </a>
                        </div>
                    </div>
                </div>
 
                <!-- Error State -->
                <div id="error-state" style="display: none;" class="text-center py-12">
                    <div class="text-6xl mb-4">⚠️</div>
                    <p class="text-xl font-semibold text-gray-800 mb-2" id="error-message">Error Loading Data</p>
                    <button onclick="location.reload()" 
                            class="btn-gradient text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition mt-4">
                        Retry
                    </button>
                </div>
            </div>
        </div>
    </div>