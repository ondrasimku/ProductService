@echo off
setlocal

rem Check if an argument is provided
if "%~1"=="" (
    echo Usage: execute ^<command^>
    exit /b 1
)

rem Run the docker-compose exec command
docker-compose exec product-service-symfony %*

endlocal