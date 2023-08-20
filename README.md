Simple application to demonstrate uploading files to minIO bucket

How to run the application:-
Edit below environment variables in 'docker-compose.yml' file
    - ACCESS_KEY='your-access-key'
    - SECRET_KEY='your-secret-access-key'
    - ENDPOINT='endpoint-url-of-minIO-server'
    - BUCKET='bucket-name'

And run application by executing 'docker-compose up' in terminal