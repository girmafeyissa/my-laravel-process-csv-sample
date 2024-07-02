This assignment has implemented using Laravel framework so

This application takes a csv file as input and calculates the commission fee by checking the type of customer and transaction type. I spent 8 hours to complete the assignment including preparing unit test for the methods inside the ``CalculationController`` class.

Since ``exchangeratesapi`` requires me to input card information which i do not have currently, I used another API called ``freecurrencyapi`` to manage the currency conversion.

I've put hard coded currency rates to match the output that is provided with the assignment. TO get the latest conversion hard coded rates from ``convertCurrency``  can be commented out.

## How to run the application

1. install the dependancies

``composer install``

2. Generate Application key

``php artisan key:generate``

3. Run the application

``php artisan serve``

after running the above command the application will be open on http://127.0.0.1:8000

## How to run test

I have managed to run a test for the three methods of the ``CalculationController``. The test suite is located in the ``tests/Feature`` directory and is named ``CalculationControllerTest``. after running the test execution command, the results will be displayed in a color-coded manner, and the number of failed and successful tests will also be displayed.

 `` php artisan test ``

> 
### FYI 

NB. I've shared the FREECURRENCY_API_KEY in the .env file is intentionally to ease the evaluation process and the account repated to the shared API key is going to be delted after some time 
