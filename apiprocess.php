<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
class Reponser
{

    private function dbConnect()
    {
        $hostname = 'localhost';
        $username = 'root';
        $password = 'BATTULAvarshini@36';
        $dbname = 'e_commerces_page';

        try {
            $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            return "Connection failed: " . $e->getMessage();
        }

    }
    public function ResProcesser($method, $main_req): void
    {
        $connect = $this->dbConnect();
        // if ($method === "POST" && $main_req === "addproduct") {
        //     try {
        //         $body = file_get_contents("php://input");
        //         parse_str($body, $queryParams);
        //         $name = $queryParams["name"];
        //         $price = $queryParams["price"];
        //         $description = $queryParams["description"];
        //         $category = $queryParams["category"];
        //         $imgUrl = $queryParams["imageUrl"];
        //         $sqlOf = "INSERT INTO product(name, category, description, img_url, price) VALUES('$name', '$category', '$description', '$imgUrl', $price)";

        //         $insert = $connect->prepare($sqlOf);
        //         $insert->execute();

        //         $data = $connect->prepare("SELECT * FROM product");
        //         $data->execute();
        //         echo json_encode($data->fetchAll(PDO::FETCH_ASSOC));
        //     } catch (err) {
        //         echo "error while adding product";
        //     }
        // }
        if ($method === "GET" && $main_req === "products") {
            $userInput = $_GET['userInput'];
            $pattern = "%$userInput%";
            // echo $userInput;
            $data = $connect->prepare("SELECT * FROM product WHERE description LIKE ? OR category LIKE ?");
            $data->execute([$pattern, $pattern]);

            $result = $data->fetchAll(PDO::FETCH_ASSOC);
            if (count($result) === 0) {
                $pattern = "%A%";
                $data = $connect->prepare("SELECT * FROM product WHERE description LIKE ?");
                $data->execute([$pattern]);
                echo json_encode($data->fetchAll(PDO::FETCH_ASSOC));
            } else {
                echo json_encode($result);
            }
        } else if ($method === "POST" && $main_req === "signup") {
            $body = file_get_contents("php://input");
            parse_str($body, $queryParams);
            $userName = $queryParams["userName"];
            $phoneNumber = $queryParams["phoneNumber"];
            $email = $queryParams["email"];
            $password = $queryParams["password"];
            $adderss = $queryParams["adderss"];

            if (strlen($userName) < 2) {
                $res = [
                    'message' => 'the length of name should be above 2',
                    'status' => 404

                ];
                echo json_encode($res);
            } else {
                if (!preg_match('/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/', $email)) {
                    $res = [
                        'message' => 'The length of the email should be above 5 characters',
                        'status' => 404

                    ];
                    echo json_encode($res);

                } else {
                    if (strlen($password) < 4) {
                        $res = [
                            'message' => 'The length of the Password should be above 3 characters',
                            'status' => 404

                        ];
                        echo json_encode($res);

                    } else {

                        $encode_password = password_hash($password, PASSWORD_DEFAULT);
                        $letrun = $connect->prepare("SELECT * FROM user where email = '$email'");
                        $letrun->execute();
                        $result = $letrun->fetchAll(PDO::FETCH_ASSOC);
                        if (count($result) > 0) {
                            $res = [
                                'message' => 'Email already exit',
                                'status' => 404

                            ];
                            echo json_encode($res);

                        } else {
                            try {
                                $insertQuery = $connect->prepare("INSERT INTO user (name, mobile_number, email, password, adderss) VALUES ('$userName', $phoneNumber, '$email', '$encode_password', '$adderss')");
                                $insertQuery->execute();
                                $letrun = $connect->prepare("SELECT * FROM user where email = '$email'");
                                $letrun->execute();
                                $isinsert = $letrun->fetchAll(PDO::FETCH_ASSOC);
                                if (count($isinsert) > 0) {
                                    $res = [
                                        'message' => 'Registation Success',
                                        'status' => 200

                                    ];
                                    echo json_encode($res);
                                } else {
                                    $res = [
                                        'message' => 'Error While create user',
                                        'status' => 502

                                    ];
                                    echo json_encode($res);
                                }
                            } catch (err) {
                                $res = [
                                    'message' => 'Error While create user',
                                    'status' => 502

                                ];
                                echo json_encode($res);
                            }


                        }

                    }

                }

            }
        } else if ($method === "POST" && $main_req === "login") {
            $body = file_get_contents("php://input");
            parse_str($body, $queryParams);
            $email = $queryParams["email"];
            $password = $queryParams["password"];

            $password = $_POST["password"] ?? null;
            $email = $_POST['email'] ?? null;
            try {
                if ($password !== null) {
                    $let_check_user = $connect->prepare("SELECT * FROM user WHERE email = :email");
                    $let_check_user->bindParam(':email', $email, PDO::PARAM_STR);
                    $let_check_user->execute();
                    $user = $let_check_user->fetchAll(PDO::FETCH_ASSOC);

                    if (count($user) > 0) {
                        $old_password = $user[0]["password"];
                        $name = $user[0]['name'];
                        $email = $user[0]['email'];
                        $id = $user[0]['user_id'];
                        $phoneNumber = $user[0]['mobile_number'];
                        $adderss = $user[0]['adderss'];
                        if (password_verify($password, $old_password)) {
                            $res = [
                                'message' => 'User Checking Success',
                                'status' => 200,
                                "user_id" => $id,
                                "user_name" => $name,
                                "email" => $email,
                                "Number" => $phoneNumber,
                                "adderss" => $adderss


                            ];
                            echo json_encode($res);

                        } else {
                            $res = [
                                'message' => 'Invalid Password/email',
                                'status' => 502,


                            ];
                            echo json_encode($res);
                        }

                    } else {
                        $res = [
                            'message' => 'Invalid Password/email',
                            'status' => 404,


                        ];
                        echo json_encode($res);
                    }
                } else {
                    $res = [
                        'message' => 'Please enter required email and password',
                        'status' => 502,


                    ];
                    echo json_encode($res);

                }
            } catch (err) {
                $res = [
                    'message' => 'Error while login',
                    'status' => 502,


                ];
                echo json_encode($res);
            }

        } else if ($method === "POST" && $main_req === "Cart") {
            $body = file_get_contents("php://input");
            parse_str($body, $queryParams);
            $userId = $queryParams["userId"];
            $productId = $queryParams['productId'];

            try {
                $letcheckProductExist = $connect->prepare("SELECT * FROM cart where user_id = '$userId' AND product_id = $productId");
                $letcheckProductExist->execute();
                $result = $letcheckProductExist->fetchAll(PDO::FETCH_ASSOC);
                if (count($result) > 0) {
                    $res = [
                        'message' => 'Success',
                        'status' => 200

                    ];
                    echo json_encode($res);

                } else {
                    $insertQuery = $connect->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES ($userId, $productId, 1)");
                    $insertQuery->execute();
                    $res = [
                        'message' => 'Success',
                        'status' => 200,


                    ];
                }

                echo json_encode($res);
            } catch (err) {
                $res = [
                    'message' => 'error',
                    'status' => 404,


                ];
                echo json_encode($res);
            }
        } else if ($method === "DELETE" && $main_req === "Cart") {
            $body = file_get_contents("php://input");
            parse_str($body, $queryParams);
            $userId = $_GET['userId'] ?? $queryParams["userId"];
            $productId = $_GET['productId'] ?? $queryParams['productId'];
            // echo $userId;
            // echo $productId;
            try {
                $letcheckDelete = $connect->prepare("DELETE FROM cart where user_id = $userId AND product_id = $productId");
                $letcheckDelete->execute();
                $letcheckProductExist = $connect->prepare("SELECT * FROM cart where user_id = $userId AND product_id = $productId");
                $letcheckProductExist->execute();
                $result = $letcheckProductExist->fetchAll(PDO::FETCH_ASSOC);
                if (count($result) === 0) {
                    $res = [
                        'message' => 'Success',
                        'status' => 200

                    ];
                    echo json_encode($res);

                } else {
                    $res = [
                        'message' => 'Error',
                        'status' => 404,


                    ];
                    echo json_encode($res);
                }

            } catch (err) {
                $res = [
                    'message' => 'error',
                    'status' => 404,


                ];
                echo json_encode($res);
            }

        } else if ($method === "PUT" && $main_req === "Cart") {
            $body = file_get_contents("php://input");
            parse_str($body, $queryParams);
            $userId = $_REQUEST['userId'] ?? $queryParams["userId"];
            $productId = $_REQUEST['productId'] ?? $queryParams['productId'];
            $quantity = $_REQUEST['quantity'] ?? $queryParams['quantity'];
            echo $userId;
            echo $productId;
            try {
                $letcheckDelete = $connect->prepare("UPDATE cart SET quantity = $quantity where user_id = $userId AND product_id = $productId");
                $letcheckDelete->execute();
                $letcheckProductExist = $connect->prepare("SELECT * FROM cart where user_id = $userId AND product_id = $productId");
                $letcheckProductExist->execute();
                $result = $letcheckProductExist->fetchAll(PDO::FETCH_ASSOC);
                if (count($result) !== 0) {
                    $res = [
                        'message' => 'Success',
                        'status' => 200

                    ];
                    echo json_encode($res);

                } else {
                    $res = [
                        'message' => 'Error',
                        'status' => 404,


                    ];
                    echo json_encode($res);
                }

            } catch (err) {
                $res = [
                    'message' => 'error',
                    'status' => 404,


                ];
                echo json_encode($res);
            }

        } else if ($method === "GET" && $main_req === "cart") {
            $userId = $_GET["userId"];
            try {
                $productsInCart = $connect->prepare("SELECT product_id, quantity FROM cart WHERE user_id = $userId");
                $productsInCart->execute();
                $result = $productsInCart->fetchAll(PDO::FETCH_ASSOC);
                $res = [
                    'message' => 'Success',
                    'products' => $result,
                    'status' => 200,


                ];
                echo json_encode($res);
            } catch (err) {
                $res = [
                    'message' => 'error',
                    'status' => 404,


                ];
                echo json_encode($res);
            }

        } else if ($method === "POST" && $main_req === "order") {
            $body = file_get_contents("php://input");
            parse_str($body, $queryParams);
            $buyngItems = json_encode($queryParams["buyingItems"]);
            $userId = $queryParams["userId"];
            $insertOrder = $connect->prepare("INSERT INTO `order`(user_id, order_item) VALUES (?,?)");
            $insertOrder->execute([$userId, $buyngItems]);
            $checkIsInsert = $connect->prepare("SELECT * FROM `order` WHERE user_id = $userId");
            $checkIsInsert->execute();
            $result = $checkIsInsert->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);

        } else {
            echo "hiii";
        }
    }
}

?>