#!/bin/bash

# Start Production Services for Railway
# This script starts both the Python MIDI service and PHP web server

set -e

echo "🚀 Starting Mess o Midi Production Services..."

# Set PORT environment variable (Railway provides this)
PORT=${PORT:-8080}

# Start Python MIDI service in the background
echo "Starting Python MIDI service on port 5001..."
cd python_service
python3 app.py &
PYTHON_PID=$!
cd ..

# Give Python service time to start
sleep 2

# Check if Python service started successfully
if ! kill -0 $PYTHON_PID 2>/dev/null; then
    echo "❌ Python service failed to start"
    exit 1
fi

echo "✅ Python service started (PID: $PYTHON_PID)"

# Start PHP web server (in foreground so Railway keeps it running)
echo "Starting PHP web server on port $PORT..."
php -S 0.0.0.0:$PORT

# If PHP exits, kill Python service
kill $PYTHON_PID 2>/dev/null

