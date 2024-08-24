# composer install
# Here I have two Models 1. Product 2. Order
# And the code of stripe payment is written in ProductController.php
Steps of the code for later understanding
1. Firstly we have installed stripe sdk using composer => composer require stripe/stripe-php
2. Then I have got the stripe secret key from stripe dashboard
3. Then I have created a route for checkout and this route uses checkout function in ProductController.
4. Here I have created an instance of stripe client using secret key
5. Then I have created the customer which we can use when the payment is successfull
6. Then I have got all the products which will be checked out.
7. Then I have fill all the details of product and price in line_items variable which is an array.
8. The line_items variable structure should be same as of now I have got that from stripe docs.
9. Then I have checkout the stripe session and here We have to pass two urls one is success and one cancel
10. Success is going to execute when our payment is done sucessfully else cancel will call.
11. Then we have to store the session id of payment in a table called orders
12. In Sucess we will get the customer information using retrive method and we can use it in our view
13. For webhook implementation we have to fisrt disable the csrf token for webhook-url route in bootstrap/app.php.
14. Then we have to download a stripe cli for testing in local
15. Afterthat we have to locate our cmd to that location where the cli tool is downloaded and the have to run 
stripe.exe login
16. And then we have to run stripe listen --forward-to localhost:8000/webhook-url. It will listen for our url.
17. Now we can  apply our logic in webhook function in ProductController   