#!/bin/bash

# Check if an argument is provided
if [ -z "$1" ]; then
    echo "Usage: $0 <command>"
    exit 1
fi

# Run the docker-compose exec command
docker-compose exec product-service-symfony "$@"