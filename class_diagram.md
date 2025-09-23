```mermaid
classDiagram
    direction LR

    class Model {
        <<abstract>>
    }
    class Controller {
        <<abstract>>
    }

    package "Controllers" {
        AuthController --|> Controller
        BookingController --|> Controller
        CheckinController --|> Controller
        CityController --|> Controller
        FlightController --|> Controller
        MessageController --|> Controller
        NewsController --|> Controller
        PromotionController --|> Controller
        SeatController --|> Controller

        package "Admin" {
            UserAdminController --|> Controller
            NewsAdminController --|> Controller
            FlightAdminController --|> Controller
        }
    }

    package "Models" {
        User --|> Model
        Role --|> Model
        Booking --|> Model
        BookingPassenger --|> Model
        Flight --|> Model
        City --|> Model
        Seat --|> Model
        Ticket --|> Model
        Checkin --|> Model
        Payment --|> Model
        Card --|> Model
        News --|> Model
        Promotion --|> Model
        Message --|> Model
        SeatChange --|> Model
        Luggage --|> Model
        Refund --|> Model
        SearchLog --|> Model
        Recommendation --|> Model
        NewsSubscription --|> Model
        Cart --|> Model
        CartItem --|> Model
        Media --|> Model
    }

    ' --- Controller to Model Relationships ---
    AuthController ..> User : uses
    AuthController ..> Role : uses
    BookingController ..> Booking : uses
    BookingController ..> Flight : uses
    BookingController ..> Seat : uses
    BookingController ..> Ticket : uses
    CheckinController ..> Checkin : uses
    CheckinController ..> Ticket : uses
    CityController ..> City : uses
    FlightController ..> Flight : uses
    FlightController ..> SearchLog : uses
    MessageController ..> Message : uses
    MessageController ..> User : uses
    NewsController ..> News : uses
    PromotionController ..> Promotion : uses
    PromotionController ..> Flight : uses
    SeatController ..> Seat : uses
    SeatController ..> BookingPassenger : uses
    SeatController ..> SeatChange : uses
    UserAdminController ..> User : uses
    UserAdminController ..> Role : uses
    FlightAdminController ..> Flight : uses
    FlightAdminController ..> Seat : uses
    FlightAdminController ..> News : uses

    ' --- Model to Model Relationships ---
    User "1" -- "many" Booking : has
    User "1" -- "many" Card : has
    User "1" -- "many" Message : "sends/receives"
    User "1" -- "many" SearchLog : has
    User "1" -- "many" Recommendation : has
    User "1" -- "1" NewsSubscription : has
    User "1" -- "1" Cart : has
    Role "1" -- "many" User : has

    Flight "1" -- "many" Booking : has
    Flight "1" -- "many" Seat : has
    Flight "1" -- "many" Promotion : has
    Flight "1" -- "many" News : has
    Flight "1" -- "many" CartItem : contains
    City "1" -- "many" Flight : "origin / destination"

    Booking "1" -- "1" User : belongs to
    Booking "1" -- "1" Flight : belongs to
    Booking "1" -- "many" BookingPassenger : has
    Booking "1" -- "many" Ticket : has
    Booking "1" -- "many" Payment : "payable"

    BookingPassenger "1" -- "1" Booking : belongs to
    BookingPassenger "1" -- "1" Seat : assigned
    BookingPassenger "1" -- "1" Ticket : has
    BookingPassenger "1" -- "1" SeatChange : has

    Ticket "1" -- "1" Booking : belongs to
    Ticket "1" -- "1" BookingPassenger : for
    Ticket "1" -- "1" Checkin : has
    Ticket "1" -- "many" Luggage : has

    Seat "1" -- "1" Flight : belongs to
    Checkin "1" -- "1" Ticket : for

    Payment "1" -- "1" User : by
    Payment "1" -- "1" Card : via
    Payment "1" -- "1" Refund : has

    Cart "1" -- "many" CartItem : has

    Media "1" -- "*" : mediable (morph)
```
