## MyJar message queue test app

Message queue processor

### Logic

1. Connects to AMQP (RabbitMQ) server (details below)
2. Listens on 'interest-queue' queue in default exchange for messages
3. For each message it calculates the "interest" and total sum by formula given below
4. Broadcast the new messages to 'solved-interest-queue' in the same exchange

When a message is received from the interest-queue, new Laravel job is created and dispatched, the Job will process the message and then push it back to the interest-solved-queue

### Message Format
Messages are transmitted as JSON.
Incoming messages will look like following:{ sum: 123, days: 5 }
Outgoing messages should look like following:
{ sum: 123, days: 5, interest: 18.45, totalSum: 141.45, token: "myIdentifier" }
{"sum":693,"days":15,"interest":297.99,"totalSum":990.99,"token":"MyJarTest"}

### Configuration

1. app.php => name - is used to determine token used in the outgoing message
2. app.php => interestRates - interest rates table, see InterestCalculatorService.php for example
3. .env - RabbitMQ connection configuration, see .env.example for example

### Setup

1. Run composer to get application dependencies
2. setup into root filed .env file, use .env.example as template

### Running

1. Setup laravel job processor using artisan commands such as queue:listen or queue:work, see more at http://laravel.com/docs/5.1/queues.
2. Activate interest queue listener from console using artisan command interest-queue:listen
* If you are using default laravel sync job queue driver this step 1 is not required

### Author

Meelis-Marius Pinka
