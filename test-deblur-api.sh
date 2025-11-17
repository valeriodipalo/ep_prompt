#!/bin/bash

# Test script for NAFNet Deblur API
# Usage: ./test-deblur-api.sh [base_url]

# Configuration
BASE_URL="${1:-http://localhost:8001}"
TEST_IMAGE_URL="https://storage.googleapis.com/falserverless/nafnet/blurry.png"

echo "======================================"
echo "NAFNet Deblur API Test"
echo "======================================"
echo "Base URL: $BASE_URL"
echo ""

# Test 1: Deblur with external URL
echo "Test 1: Deblur with external image URL"
echo "--------------------------------------"
RESPONSE=$(curl -s -X POST "$BASE_URL/api/fal/deblur" \
  -H "Content-Type: application/json" \
  -d "{
    \"image_url\": \"$TEST_IMAGE_URL\"
  }")

echo "Response:"
echo "$RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$RESPONSE"
echo ""

# Check if response is successful
if echo "$RESPONSE" | grep -q '"success":true'; then
    echo "✅ Test 1 PASSED: API returned success"
    IMAGE_URL=$(echo "$RESPONSE" | grep -o '"image":"[^"]*"' | cut -d'"' -f4)
    echo "   Deblurred image URL: $IMAGE_URL"
else
    echo "❌ Test 1 FAILED: API returned error"
fi

echo ""
echo "======================================"

# Test 2: Deblur with seed parameter
echo "Test 2: Deblur with seed parameter"
echo "--------------------------------------"
RESPONSE2=$(curl -s -X POST "$BASE_URL/api/fal/deblur" \
  -H "Content-Type: application/json" \
  -d "{
    \"image_url\": \"$TEST_IMAGE_URL\",
    \"seed\": 42
  }")

echo "Response:"
echo "$RESPONSE2" | python3 -m json.tool 2>/dev/null || echo "$RESPONSE2"
echo ""

if echo "$RESPONSE2" | grep -q '"success":true'; then
    echo "✅ Test 2 PASSED: API accepted seed parameter"
else
    echo "❌ Test 2 FAILED: API returned error with seed"
fi

echo ""
echo "======================================"

# Test 3: Error handling - missing image_url
echo "Test 3: Error handling (missing image_url)"
echo "--------------------------------------"
RESPONSE3=$(curl -s -X POST "$BASE_URL/api/fal/deblur" \
  -H "Content-Type: application/json" \
  -d "{}")

echo "Response:"
echo "$RESPONSE3" | python3 -m json.tool 2>/dev/null || echo "$RESPONSE3"
echo ""

if echo "$RESPONSE3" | grep -q '"success":false'; then
    echo "✅ Test 3 PASSED: API correctly handles validation error"
else
    echo "❌ Test 3 FAILED: API should return validation error"
fi

echo ""
echo "======================================"
echo "Test Summary"
echo "======================================"
echo ""
echo "All tests completed!"
echo ""
echo "Next steps:"
echo "1. Verify the deblurred images look correct"
echo "2. Test credit deduction with consume-transformation endpoint"
echo "3. Integrate with your frontend application"
echo ""
echo "For more details, see: NAFNET_DEBLUR_API.md"

