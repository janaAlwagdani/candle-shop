
-- Database: `candlecy_store`
--

-- ------------------
--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminID` int(11) NOT NULL,
  `adminEmail` varchar(100) NOT NULL,
  `adminPassword` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminID`, `adminEmail`, `adminPassword`) VALUES
(1, 'admin@candlecystore.com', 'admin123'),


-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `product_image` varchar(255) DEFAULT NULL,
  `guest_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checkout`
--

CREATE TABLE `checkout` (
  `id` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `productName` varchar(255) NOT NULL,
  `productPrice` decimal(10,2) NOT NULL,
  `productQuantity` int(11) NOT NULL,
  `productImage` varchar(255) NOT NULL,
  `guest_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `productID` int(11) NOT NULL,
  `productName` varchar(100) NOT NULL,
  `productImage` varchar(255) NOT NULL,
  `productDescription` text DEFAULT NULL,
  `productPrice` decimal(10,2) DEFAULT NULL,
  `productStock` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`productID`, `productName`, `productImage`, `productDescription`, `productPrice`, `productStock`) VALUES
(1, 'Vanilla- Golden Bliss', 'images/products/680ec28cb05bc.png', 'Vanilla-Golden Bliss is a small, luxurious candle that blends the sweet warmth of vanilla with a touch of golden elegance for a soothing, indulgent experience.', 90.00, 4),
(2, 'Papaya: Tropical Glow', 'images/Classic/Candle2.png', 'Papaya: Tropical Glow is a vibrant candle that captures the essence of ripe papaya, filling the air with a fresh, exotic fragrance that transports you to a tropical paradise.', 89.99, 0),
(3, 'Coconut: Island Serenity', 'images/products/680eca001e8d2.png', 'Coconut: Island Serenity is a tropical-scented candle that blends creamy coconut with subtle hints of island florals, evoking a calming and relaxing escape to paradise.', 89.00, 5),
(4, 'Peach: Velvet Sunset', 'images/Classic/Candle4.png', 'Peach: Velvet Sunset is a fragrant candle that combines the sweet, juicy essence of ripe peaches with a smooth, warm touch of sunset magic.', 89.99, 40),
(5, 'Chocolate: Cocoa Indulgence', 'images/Classic/Candle5.png', 'Chocolate: Cocoa Indulgence is a rich and decadent candle that wraps your senses in the deep, comforting aroma of creamy cocoa and warmth.', 89.99, 65),
(6, 'Pomegranate: Ruby Essence', 'images/Classic/Candle6.png', 'Pomegranate: Ruby Essence is a vibrant candle that energizes your space with the juicy, ruby-like fragrance of ripe pomegranates.', 89.99, 0),
(21, 'Fireside Glow: Smoky wood warmth', 'images/Winter/Winter1.png', 'Fireside Glow offers the warmth of smoky wood with comforting spice notes, perfect for cozy winter evenings.', 95.50, 5),
(22, 'Frosted Pine: Crisp pine scent', 'images/Winter/Winter2.png', 'Frosted Pine captures the essence of a crisp winter forest, with a clean, invigorating pine aroma.', 95.50, 8),
(23, 'Warm Vanilla Snow: Soft vanilla frost', 'images/Winter/Winter3.png', 'Warm Vanilla Snow combines the sweetness of vanilla with a frosty freshness for a wintery delight.', 95.50, 0),
(24, 'Cozy Cabin: Spices and wood', 'images/Winter/Winter4.png', 'Cozy Cabin blends the warmth of spices and wood, perfect for relaxing evenings indoors.', 74.99, 44),
(25, 'Cedarwood Chill: Earthy cedar coolness', 'images/Winter/Winter5.png', 'Cedarwood Chill blends earthy cedarwood with cool, refreshing notes for a grounding winter scent.', 74.99, 28),
(26, 'Winter Wonderland: Mountain Tea', 'images/Winter/Winter6.png', 'Winter Wonderland combines the sweetness of vanilla and floral tea with a hint of crisp mountain air.', 74.99, 10);

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `guest_id` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `product_description` text DEFAULT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `purchase_date` datetime DEFAULT current_timestamp(),
  `product_quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `guest_id`, `product_id`, `product_name`, `product_image`, `product_description`, `product_price`, `purchase_date`, `product_quantity`) VALUES

-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adminID`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `checkout`
--
ALTER TABLE `checkout`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`productID`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=244;

--
-- AUTO_INCREMENT for table `checkout`
--
ALTER TABLE `checkout`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `productID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;
COMMIT;
