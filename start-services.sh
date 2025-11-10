#!/bin/bash
# Start both PHP and Python services for Mess o Midi

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Starting Mess o Midi Services...${NC}\n"

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo -e "${YELLOW}Python 3 is not installed. Please install Python 3 to run the MIDI generation service.${NC}"
    exit 1
fi

# Start Python service in background
echo -e "${GREEN}Starting Python MIDI Generation Service...${NC}"
cd python_service

# Check if virtual environment exists, if not create it
if [ ! -d "venv" ]; then
    echo -e "${YELLOW}Creating Python virtual environment...${NC}"
    python3 -m venv venv
fi

# Activate virtual environment
source venv/bin/activate

# Install/update requirements
echo -e "${YELLOW}Installing Python dependencies...${NC}"
pip install -q -r requirements.txt

# Start Python service in background
echo -e "${GREEN}Python service starting on http://localhost:5000${NC}"
python app.py &
PYTHON_PID=$!

# Return to main directory
cd ..

# Start PHP service
echo -e "\n${GREEN}Starting PHP Web Server...${NC}"
echo -e "${GREEN}PHP service starting on http://localhost:9000${NC}"
php -S localhost:9000 &
PHP_PID=$!

echo -e "\n${GREEN}✓ Services Started!${NC}"
echo -e "  - PHP Web Server: http://localhost:9000"
echo -e "  - Python API: http://localhost:5001"
echo -e "\nPress Ctrl+C to stop all services\n"

# Function to cleanup on exit
cleanup() {
    echo -e "\n${YELLOW}Stopping services...${NC}"
    kill $PYTHON_PID 2>/dev/null
    kill $PHP_PID 2>/dev/null
    echo -e "${GREEN}Services stopped.${NC}"
    exit 0
}

# Trap Ctrl+C
trap cleanup SIGINT SIGTERM

# Wait for services (keeps script running)
wait

