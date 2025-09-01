<?php
// app/Http/Controllers/TripController.php

namespace App\Http\Controllers;

use App\Http\Requests\TripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TripController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of trips with pagination and search
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Trip::with('user:id,name,email');

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $query->search($request->search);
            }

            // Date filtering
            if ($request->has('date') && !empty($request->date)) {
                $query->byDate($request->date);
            }

            // Date range filtering
            if ($request->has('start_date') && !empty($request->start_date)) {
                $endDate = $request->has('end_date') ? $request->end_date : null;
                $query->byDateRange($request->start_date, $endDate);
            }

            // Status filtering
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            } else {
                // Default to active trips only
                $query->active();
            }

            // Location filtering
            if ($request->has('departure') && !empty($request->departure)) {
                $query->where('departure', 'like', "%{$request->departure}%");
            }

            if ($request->has('destination') && !empty($request->destination)) {
                $query->where('destination', 'like', "%{$request->destination}%");
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'departure_date');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Pagination
            $perPage = min($request->get('per_page', 15), 50); // Max 50 items per page
            $trips = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $trips,
                'message' => 'Trips retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve trips',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created trip
     */
    public function store(TripRequest $request): JsonResponse
    {
        try {
            $tripData = $request->validated();
            $tripData['user_id'] = auth()->id();

            $trip = Trip::create($tripData);
            $trip->load('user:id,name,email');

            return response()->json([
                'success' => true,
                'data' => $trip,
                'message' => 'Trip created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create trip',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified trip
     */
    public function show(Trip $trip): JsonResponse
    {
        try {
            $trip->load('user:id,name,email,phone');

            return response()->json([
                'success' => true,
                'data' => $trip,
                'message' => 'Trip retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve trip',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified trip (only if user owns it)
     */
    public function update(UpdateTripRequest $request, Trip $trip): JsonResponse
    {
        try {
            // Check if user owns the trip
            if ($trip->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only modify your own trips.'
                ], 403);
            }

            $trip->update($request->validated());
            $trip->load('user:id,name,email');

            return response()->json([
                'success' => true,
                'data' => $trip,
                'message' => 'Trip updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update trip',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified trip (only if user owns it)
     */
    public function destroy(Trip $trip): JsonResponse
    {
        try {
            // Check if user owns the trip
            if ($trip->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only delete your own trips.'
                ], 403);
            }

            $trip->delete();

            return response()->json([
                'success' => true,
                'message' => 'Trip deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete trip',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trips belonging to the authenticated user
     */
    public function myTrips(Request $request): JsonResponse
    {
        try {
            $query = auth()->user()->trips();

            // Status filtering
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Date filtering
            if ($request->has('date') && !empty($request->date)) {
                $query->byDate($request->date);
            }

            // Date range filtering
            if ($request->has('start_date') && !empty($request->start_date)) {
                $endDate = $request->has('end_date') ? $request->end_date : null;
                $query->byDateRange($request->start_date, $endDate);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'departure_date');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            // Pagination
            $perPage = min($request->get('per_page', 15), 50);
            $trips = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $trips,
                'message' => 'Your trips retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve your trips',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a trip (change status to cancelled)
     */
    public function cancel(Trip $trip): JsonResponse
    {
        try {
            // Check if user owns the trip
            if ($trip->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only cancel your own trips.'
                ], 403);
            }

            if ($trip->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Trip is already cancelled'
                ], 400);
            }

            $trip->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'data' => $trip,
                'message' => 'Trip cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel trip',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete a trip (change status to completed)
     */
    public function complete(Trip $trip): JsonResponse
    {
        try {
            // Check if user owns the trip
            if ($trip->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only complete your own trips.'
                ], 403);
            }

            if ($trip->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Trip is already completed'
                ], 400);
            }

            $trip->update(['status' => 'completed']);

            return response()->json([
                'success' => true,
                'data' => $trip,
                'message' => 'Trip marked as completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete trip',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
