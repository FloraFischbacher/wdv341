-- Adapted version of this SQL file for PostgreSQL, the database I've been
-- using as I've been working on this. It has some dialectical differences in
-- comparison to MySQL/MariaDB, so I had to fix it slightly ^^'
--
-- Nothing should have changed about the contents, though!

START TRANSACTION;
SET TIME ZONE DEFAULT;

DROP TABLE IF EXISTS wdv341_events;
CREATE TABLE wdv341_events (
    event_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    event_name varchar(50),
    event_description text,
    event_presenter varchar(50),
    event_date date,
    event_time time,
    event_date_inserted date, 
    event_date_updated date
);

INSERT INTO wdv341_events (
        event_name,
        event_description,
        event_presenter,
        event_date,
        event_time,
        event_date_inserted,
        event_date_updated
    )
VALUES (
        'Static Typing Discussion',
        'An exploration into the world of static typing, and how static types may improve code robustness.',
        'Ellie Mitchell',
        '2025-11-11',
        '05:00:00',
        '2025-10-07',
        '2025-10-07'
    ), (
        'Networking for Wallflowers',
        'An introduction to the principles of networks: the way in which computers communicate with one another.',
        'Wyatt Martinez',
        '2025-11-11',
        '13:00:00',
        '2025-10-07',
        '2025-10-07'
    ), (
        'What Happened to Enron?',
        'A tale of... a lot of fraud, mostly',
        'Aurora Garcia',
        '2025-11-11',
        '09:00:00',
        '2025-10-07',
        '2025-10-07'
    );

DROP TABLE IF EXISTS wdv341_products;
CREATE TABLE wdv341_products (
    product_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    product_name varchar(250) NOT NULL,
    product_description varchar(500) NOT NULL,
    product_price decimal(10, 2) NOT NULL,
    product_image varchar(250) NOT NULL,
    product_inStock integer NOT NULL,
    product_status varchar(250) NOT NULL,
    product_update_date date NOT NULL
);

INSERT INTO wdv341_products (
        product_name,
        product_description,
        product_price,
        product_image,
        product_inStock,
        product_status,
        product_update_date
    )
VALUES (
        '2TB External Hard Drive',
        '2.0 Terrabytes of storage. This USB devices has fast access speed to safely backup your vital information. A red protective case also included.',
        '129.99',
        'externalHardDrive.jpg',
        27,
        '',
        '2020-10-05'
    ),
    (
        '500GB Flash Drive',
        '500GB USB flash drive. With sliding protective cover. Bright red body makes it easier to see and find. ',
        '19.95',
        'flashDrive.jpg',
        289,
        'BONUS: Silver 24GB Flash Drive included!',
        '2020-10-01'
    ),
    (
        'Office Headset ',
        'Home office headset with boom mike. USB connection with 2 meter cable provides flexibility. Comfort ear coverings. Sound dampening for better control. ',
        '29.95',
        'headphones.jpg',
        9,
        '',
        '2020-10-02'
    ),
    (
        'Desktop Microphone',
        'USB Computer Microphone.  24" cord.  Base mounted pushbutton for Mute/Unmute. Flexible neck allows for better positioning.',
        '42.99',
        'microphone.jpg',
        36,
        'New item!!',
        '2020-10-06'
    ),
    (
        '27" Monitor',
        '27" LED Flat screen monitor. Solid base for desktop usage. Good choice for home office and school work.',
        '159.99',
        'monitor.jpg',
        89,
        '',
        '2020-09-16'
    ),
    (
        'Web Camera',
        'Flexible mount web camera. Limited angle focus keeps you in the picture and reduces background clutter. Built in microphone available. USB or wireless options available. ',
        '89.95',
        'webCamera.jpg',
        2,
        'Limited Quantity!',
        '2020-09-08'
    );
--
-- Indexes for dumped tables
--

COMMIT;

SELECT * FROM wdv341_events;
SELECT * FROM wdv341_products;