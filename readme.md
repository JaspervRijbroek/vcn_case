# VCN Case

I was asked to work out a case for VCN.
In this project, I say how I went to work and how I have thought it out.

## The Case

There is an interface that can only be used for authorized (paying) customers to allow currency conversion.
A few resources can be used for the conversion, the former being less reliable and possibly slower.

*Available resources*:  
- https://free.currencyconverterapi.com/
- http://currencyconverter.kowabunga.net/converter.asmx

## Requirements

- All services must be able to be used and others should be able to be plugged into it with ease.
- Other requirements received on paper.

## Used libraries

- Guzzle: HTTP client that is versatile, well documented and almost a global default.
- PHP Soap: This is to interface with currencyvonverter.kowabunga.net.
- Laravel: This will hold the base endpoints. This because it is easily securable and fast also supports all other functionality that is required.
- BootstrapCSS: This is because I am not that great with CSS and will take some pain away.
- jQuery: This will handle all user input, used because it is cross platform compatible.
- TypeScript: Because we will work in bigger teams and the type definitions are a blessing in that regard.
- Select2: This is a clean, well supported dropdown with autocomplete.
- WebPack: To make everything as small as possible and not include more javascript than needed.

## My way of working

1. First I checked out the provided paper & screenshot and started thinking on what we needed.
    1. By interface I decided that select2, bootstrap and jQuery are needed to provide for a clean interface.
    2. An API is also needed for the AJAX calls that will be send to the server.
    3. Based on the requirements I decided that caching for the currencies is in order, this will be stored in the database.
    4. Because the currencies are current for one day, a cronjob will be added to sync the currencies and cache the new data,
        historic data will be saved in another database.
    5. An adapter pattern will be used for the separate resources to receive the currencies.
    6. 
    
     
2. Check out the provided resources, I decided that adapters are in place for this.
    1. The adapters will receive a static variable to give a priority for ordering.
    2. Based on this ordering the requests will be done.

