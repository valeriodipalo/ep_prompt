#!/bin/bash

# Complete Workflow Test: Deblur + Credit Deduction
# This script tests the complete workflow including authentication and credit management
# Usage: ./test-complete-workflow.sh

BASE_URL="${1:-http://localhost:8001}"
TEST_EMAIL="test-$(date +%s)@example.com"
TEST_PASSWORD="TestPassword123"
TEST_NAME="Test User"

echo "======================================"
echo "Complete Workflow Test"
echo "======================================"
echo "Base URL: $BASE_URL"
echo ""

# Step 1: Register a test user
echo "Step 1: Register test user"
echo "--------------------------------------"
REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/api/auth/register" \
  -H "Content-Type: application/json" \
  -d "{
    \"email\": \"$TEST_EMAIL\",
    \"password\": \"$TEST_PASSWORD\",
    \"name\": \"$TEST_NAME\"
  }")

echo "Registration Response:"
echo "$REGISTER_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$REGISTER_RESPONSE"
echo ""

if echo "$REGISTER_RESPONSE" | grep -q '"success":true'; then
    echo "✅ Step 1 PASSED: User registered successfully"
    ACCESS_TOKEN=$(echo "$REGISTER_RESPONSE" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)
    USER_ID=$(echo "$REGISTER_RESPONSE" | python3 -c "import sys, json; data=json.load(sys.stdin); print(data['user']['id'])" 2>/dev/null)
    INITIAL_CREDITS=$(echo "$REGISTER_RESPONSE" | python3 -c "import sys, json; data=json.load(sys.stdin); print(data['profile']['generations_remaining'])" 2>/dev/null)
    
    echo "   Access Token: ${ACCESS_TOKEN:0:20}..."
    echo "   User ID: $USER_ID"
    echo "   Initial Credits: $INITIAL_CREDITS"
else
    echo "❌ Step 1 FAILED: User registration failed"
    exit 1
fi

echo ""
echo "======================================"

# Step 2: Get user profile
echo "Step 2: Get user profile"
echo "--------------------------------------"
PROFILE_RESPONSE=$(curl -s -X GET "$BASE_URL/api/auth/profile?user_id=$USER_ID" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json")

echo "Profile Response:"
echo "$PROFILE_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$PROFILE_RESPONSE"
echo ""

if echo "$PROFILE_RESPONSE" | grep -q '"success":true'; then
    echo "✅ Step 2 PASSED: Profile retrieved successfully"
else
    echo "❌ Step 2 FAILED: Failed to retrieve profile"
fi

echo ""
echo "======================================"

# Step 3: Deblur an image
echo "Step 3: Deblur image"
echo "--------------------------------------"
TEST_IMAGE_URL="https://storage.googleapis.com/falserverless/nafnet/blurry.png"

DEBLUR_RESPONSE=$(curl -s -X POST "$BASE_URL/api/fal/deblur" \
  -H "Content-Type: application/json" \
  -d "{
    \"image_url\": \"$TEST_IMAGE_URL\"
  }")

echo "Deblur Response:"
echo "$DEBLUR_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$DEBLUR_RESPONSE"
echo ""

if echo "$DEBLUR_RESPONSE" | grep -q '"success":true'; then
    echo "✅ Step 3 PASSED: Image deblurred successfully"
    DEBLURRED_IMAGE=$(echo "$DEBLUR_RESPONSE" | grep -o '"image":"[^"]*"' | cut -d'"' -f4)
    echo "   Deblurred Image URL: $DEBLURRED_IMAGE"
else
    echo "❌ Step 3 FAILED: Image deblur failed"
    exit 1
fi

echo ""
echo "======================================"

# Step 4: Consume 1 generation credit
echo "Step 4: Consume generation credit"
echo "--------------------------------------"
CONSUME_RESPONSE=$(curl -s -X POST "$BASE_URL/api/auth/consume-transformation" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"user_id\": \"$USER_ID\",
    \"generations_to_deduct\": 1
  }")

echo "Consumption Response:"
echo "$CONSUME_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$CONSUME_RESPONSE"
echo ""

if echo "$CONSUME_RESPONSE" | grep -q '"success":true'; then
    echo "✅ Step 4 PASSED: Credit consumed successfully"
    REMAINING_CREDITS=$(echo "$CONSUME_RESPONSE" | python3 -c "import sys, json; data=json.load(sys.stdin); print(data['generations_remaining'])" 2>/dev/null)
    echo "   Remaining Credits: $REMAINING_CREDITS"
    echo "   Credits Used: 1"
else
    echo "❌ Step 4 FAILED: Failed to consume credit"
fi

echo ""
echo "======================================"

# Step 5: Verify updated profile
echo "Step 5: Verify updated profile"
echo "--------------------------------------"
FINAL_PROFILE_RESPONSE=$(curl -s -X GET "$BASE_URL/api/auth/profile?user_id=$USER_ID" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json")

echo "Final Profile Response:"
echo "$FINAL_PROFILE_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$FINAL_PROFILE_RESPONSE"
echo ""

FINAL_CREDITS=$(echo "$FINAL_PROFILE_RESPONSE" | python3 -c "import sys, json; data=json.load(sys.stdin); print(data['profile']['generations_remaining'])" 2>/dev/null)

if [ "$FINAL_CREDITS" = "$REMAINING_CREDITS" ]; then
    echo "✅ Step 5 PASSED: Profile correctly reflects credit deduction"
    echo "   Initial: $INITIAL_CREDITS → Final: $FINAL_CREDITS"
else
    echo "❌ Step 5 FAILED: Credit count mismatch"
fi

echo ""
echo "======================================"
echo "Workflow Test Summary"
echo "======================================"
echo ""
echo "Test User: $TEST_EMAIL"
echo "User ID: $USER_ID"
echo "Initial Credits: $INITIAL_CREDITS"
echo "Final Credits: $FINAL_CREDITS"
echo "Credits Used: 1"
echo ""
echo "✅ Complete workflow test finished!"
echo ""
echo "Note: This test created a real user in your database."
echo "You may want to clean up test users periodically."

