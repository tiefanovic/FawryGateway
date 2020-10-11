# FawryGateway
Magento 2 Fawry Payment Gateway
Magento2 Integration for Egyptian Fawry payment gateway

# Install
- download package and unip it in ```` app/code/Tiefanovic/FawryGateway ````
- Run the following commands
- ```` bin/magento s:up ````
- ```` bin/magento setup:di:compile ````
- ```` bin/magento setup:static-content:deploy ````

# Configuration
- Add your payment keys in ```` Stores > Configuration > Sales > Payment Methods > Fawry Gateway ````
- make sure to run Magento cron jobs to listen to successfull FawryPay payment
