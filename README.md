<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).




ab transport ka menu bnana h esme dashboard , vehicle management ,  routes  , report hogi 
pahle pura prompt pdna sahi se fir work start krna 
esme dashboard ki fist image jaise bnana h 
vehicle management me vehclies ki list dikhegi and add vehcile krne pr   Vehicle Number ,Vehicle Model (optional ),  Driver Name , Driver Phone ,Note , license number (optional ) krke create krna h 
ab routes me  pahle routes ki list dikhegi fir add route ka button jisme  Route Name ,  Period(school =>settings me period ) ,Transport Vehicles (is dropdown me vehicle management se data ayega ) , Assign Students (is me student ka data aayega ),Route amount 

 or reports me pahle class select kre fir section , transport route , transport route  select krne pr us classs ke student aa jaye jisko woh perticular trasnport assign h bs

 and main baat ab ACCOUNTING => trasnport invoices jisme transport ki invoices dikhegi and add new transport ki invoice ka button dena jha se click krne pr classs select kre , section  , Transport Route ,  Invoice Heading , invoice date ,amount (transport route select krne pr amount aana chaiye ) and starting ending date huye partial pay ka amount dekar add new trasnport invoice kr dena h or es invoice pr payment recieve krke partial payment kr sakte h or invoice ko paid , partial pay kr sakte h and esko pay krne ka sab system baki normal invoice ke jaise rakhna h bs etna krna h and yehi invoices student ke account me show honi chahiye 



 acedemic me ek ek submenu do assign subject krke 
 pahle pura prompt pdna fir work start krna
 jisme list dikhe subjects name , code , type , class (jis class ka woh subject h)  ,teachers (assign teacher or agar assign h to number of teacher (teachers name )) ,and esme click krne pr seocnd image jaise screen khole jha teacher ko subject assign kre Assign Teachers me teachers aaye and select krne pr woh subject us teacher ko assign ho jaaye 
 color theme project theme color use krna

pura prompt pdna sahi se fir work krna
data sesison wise seprate rhega eska
ab academic menu me student leaves wale submenu me student ki leaves ka data rakhna h ab isme search krne ka option dena h jha se class select kre fir section , fir status (all , approved , pending , rejected ) fir date , or student ka name search krne ka option dena h jha se select kre 
 list dikhani h students ki leave ki 
 ab ek button dena Add Student Leave krke esme
 class select krwaye then class section , student name , Number of Leave Days ( single or multiple ho sakte h ) , leave type (sick, personal , other ) ,reason , attachment (optional ) , status (approved , pending , rejected ) then add leave ke liye apply krna h 




 accounting section ,   


 remaining Registration Invoice Type: , 


 ab apne ko smtp (mail ) sending setup krna h 
 ye http://127.0.0.1:8000/sm-school/settings me email carrier krke tab bnana h usme eska setup dena 
 isme Email Carrier:(dropdown me smtp dena h bs ) , From Name:
, SMTP Host: , SMTP Username:, SMTP Password: , SMTP Encryption( tls ) , SMTP Port ( 587) , and then save ka button ho 
ye data save krna h es tab me to bs 
and 
ab http://127.0.0.1:8000/sm-school/settings esme ek or tab bnana h email  tamplate krke 
esme inside email tamplate ke andar 5-6 tamplate bnane h jse ( Student admisson email template ,invoice generated email ,fee recieved  payment email , leave application email ,attendance taken  notification email ,absent student notification email ,  due date email template , examadmit card , exam result announce email ,  ) 
or har template ke andar Student send email : (yes or not ) , You can use the following variables:   Student Name
[STUDENT_NAME], Class
[CLASS] , Section
[SECTION], Roll Number
[ROLL_NUMBER] , [ENROLLMENT_NUMBER] , [ADMISSION_NUMBER] , [SCHOOL_NAME] ..etc
according to tempplate use kr lena then email subject then email body then save ka button esme template save kr dena ab jab agr for example Student admisson email template  me Student send email yes hoga to jb student add hoga to usko email chala jayega bs eska mtlb yehi h isko sahi se implement krna 
email jana chaiye be kayda


