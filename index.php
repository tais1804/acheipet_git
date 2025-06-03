<?php
    include "dados_usuario.php";
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Achei pet</title>
        <link rel="icon" type="image/png" sizes="16x16"  href="images/favicons/favicon-16x16.png">
        <style>
            @import url(https://fonts.googleapis.com/css2?family=Lato&display=swap);
            @import url(https://fonts.googleapis.com/css2?family=Open+Sans&display=swap);
            .img-home {
                z-index: 10;
                position: relative;
            }
        </style>
        <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
        
    </head>
    <body class="bg_body_white_teste">
        <div id="webcrumbs">
            <div class="relative w-full min-h-screen bg-white">

                <?php
                    include "header.php";
                ?>
                <main class="relative w-full bg-red-500 teste-tais">
                    <div class="container mx-auto px-4 py-10 flex flex-col lg:flex-row">
                        <div class="lg:w-1/2 z-10 pr-4 opcoes-home">
                            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">O que deseja fazer hoje?</h1>
                            <p class="text-white mb-10">
                                Aqui você pode encontrar todo tipo de informação sobre adoção, doação e informações de
                                animais desaparecidos.
                            </p>
                            <div class="row">
                                
                                <div class="col-6">
                                    <div class="bg-white rounded-lg items-center hover:shadow-lg transition-shadow transform hover:translate-x-1">
                                        <a href="listar_pets.php" class="p-4 flex">
                                            <div class="mr-4">
                                                <img src="../img/img-dog.png" alt="Lost cat" class="" />
                                            </div>
                                            <div>
                                                <h3 class="text-xl font-bold text-red-500">Quero adotar um Pet</h3>
                                                <p class="text-gray-600">
                                                Ache um pet para amar mais próximo de você...
                                                </p>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white rounded-lg items-center hover:shadow-lg transition-shadow transform hover:translate-x-1">
                                        <a href="cadastrar_pet.php" class="p-4 flex">
                                            <div class="mr-4">
                                                <img src="../img/img-dog-box.png" alt="Lost cat" class="" />
                                            </div>
                                            <div>
                                                <h3 class="text-xl font-bold text-red-500">Quero doar um Pet</h3>
                                                <p class="text-gray-600">
                                                    Cadastre informações do seu pet perdido e sua última localização.
                                                </p>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <br/>
                            <div class="row">
                                <div class="col-6">
                                    <div class="bg-white rounded-lg items-center hover:shadow-lg transition-shadow transform hover:translate-x-1">
                                        <a href="cadastrar_pet_perdido.php" class="p-4 flex">
                                            <div class="mr-4">
                                                <img src="../img/img-gato.png" alt="Lost cat" class="" />
                                            </div>
                                            <div>
                                                <h3 class="text-xl font-bold text-red-500">Perdi meu Pet!</h3>
                                                <p class="text-gray-600">
                                                    Cadastre informações do seu pet perdido e sua última localização.
                                                </p>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            
                                <div class="col-6"> 
                                    <div class="bg-white rounded-lg items-center hover:shadow-lg transition-shadow transform hover:translate-x-1">
                                        <a href="listar_pet_perdido.php" class="p-4 flex">
                                            <div class="mr-4">
                                                <img src="../img/img-achei.png" alt="Lost cat" class="" />
                                            </div>
                                            <div>
                                                <h3 class="text-xl font-bold text-red-500">Achei um pet!</h3>
                                                <p class="text-gray-600">
                                                    Olhe na nossa lista de pets perdidos e encontre o tutor!
                                                </p>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-10">
                                <p class="text-white text-center mb-4">Patrocinadores</p>
                                <div class="bg-red-400 bg-opacity-30 rounded-lg p-6 patro-img">
                                    <div class="flex justify-around">
                                        <a href="https://webcrumbs.cloud/placeholder" class="text-white flex flex-col items-center hover:opacity-80 transition-opacity">
                                            
                                            <img src="../img/patro-home-pets.png" alt="AcheiPet" class="h-10 mr-2 hover:opacity-80 transition-opacity"/>
                                        </a>
                                        <a href="https://webcrumbs.cloud/placeholder" class="text-white flex flex-col items-center hover:opacity-80 transition-opacity">
                                            <img src="../img/patro-petcare.png" alt="AcheiPet" class="h-10 mr-2 hover:opacity-80 transition-opacity"/>
                                        </a>
                                        <a href="https://webcrumbs.cloud/placeholder" class="text-white flex flex-col items-center hover:opacity-80 transition-opacity">
                                            <img src="../img/patro-parrot.png" alt="AcheiPet" class="h-10 mr-2 hover:opacity-80 transition-opacity"/>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="lg:w-1/2 mt-6 lg:mt-0 flex justify-center lg:justify-end img-inicial">
                            <img
                                src="../img/mulher_capao.png"
                                alt="Woman with puppy"
                                class="max-h-[500px] object-cover rounded-lg img-home"
                            />
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                content: ["./src/**/*.{html,js}"],
                theme: {
                    name: "Bluewave",
                    fontFamily: {
                        sans: [
                            "Open Sans",
                            "ui-sans-serif",
                            "system-ui",
                            "sans-serif",
                            '"Apple Color Emoji"',
                            '"Segoe UI Emoji"',
                            '"Segoe UI Symbol"',
                            '"Noto Color Emoji"'
                        ]
                    },
                    extend: {
                        fontFamily: {
                            title: [
                                "Lato",
                                "ui-sans-serif",
                                "system-ui",
                                "sans-serif",
                                '"Apple Color Emoji"',
                                '"Segoe UI Emoji"',
                                '"Segoe UI Symbol"',
                                '"Noto Color Emoji"'
                            ],
                            body: [
                                "Open Sans",
                                "ui-sans-serif",
                                "system-ui",
                                "sans-serif",
                                '"Apple Color Emoji"',
                                '"Segoe UI Emoji"',
                                '"Segoe UI Symbol"',
                                '"Noto Color Emoji"'
                            ]
                        },
                        colors: {
                            neutral: {
                                50: "#f7f7f7",
                                100: "#eeeeee",
                                200: "#e0e0e0",
                                300: "#cacaca",
                                400: "#b1b1b1",
                                500: "#999999",
                                600: "#7f7f7f",
                                700: "#676767",
                                800: "#545454",
                                900: "#464646",
                                950: "#282828"
                            },
                            primary: {
                                50: "#f3f1ff",
                                100: "#e9e5ff",
                                200: "#d5cfff",
                                300: "#b7a9ff",
                                400: "#9478ff",
                                500: "#7341ff",
                                600: "#631bff",
                                700: "#611bf8",
                                800: "#4607d0",
                                900: "#3c08aa",
                                950: "#220174",
                                DEFAULT: "#611bf8"
                            }
                        }
                    },
                    fontSize: {
                        xs: ["12px", {lineHeight: "19.200000000000003px"}],
                        sm: ["14px", {lineHeight: "21px"}],
                        base: ["16px", {lineHeight: "25.6px"}],
                        lg: ["18px", {lineHeight: "27px"}],
                        xl: ["20px", {lineHeight: "28px"}],
                        "2xl": ["24px", {lineHeight: "31.200000000000003px"}],
                        "3xl": ["30px", {lineHeight: "36px"}],
                        "4xl": ["36px", {lineHeight: "41.4px"}],
                        "5xl": ["48px", {lineHeight: "52.800000000000004px"}],
                        "6xl": ["60px", {lineHeight: "66px"}],
                        "7xl": ["72px", {lineHeight: "75.60000000000001px"}],
                        "8xl": ["96px", {lineHeight: "100.80000000000001px"}],
                        "9xl": ["128px", {lineHeight: "134.4px"}]
                    },
                    borderRadius: {
                        none: "0px",
                        sm: "6px",
                        DEFAULT: "12px",
                        md: "18px",
                        lg: "24px",
                        xl: "36px",
                        "2xl": "48px",
                        "3xl": "72px",
                        full: "9999px"
                    },
                    spacing: {
                        0: "0px",
                        1: "4px",
                        2: "8px",
                        3: "12px",
                        4: "16px",
                        5: "20px",
                        6: "24px",
                        7: "28px",
                        8: "32px",
                        9: "36px",
                        10: "40px",
                        11: "44px",
                        12: "48px",
                        14: "56px",
                        16: "64px",
                        20: "80px",
                        24: "96px",
                        28: "112px",
                        32: "128px",
                        36: "144px",
                        40: "160px",
                        44: "176px",
                        48: "192px",
                        52: "208px",
                        56: "224px",
                        60: "240px",
                        64: "256px",
                        72: "288px",
                        80: "320px",
                        96: "384px",
                        px: "1px",
                        0.5: "2px",
                        1.5: "6px",
                        2.5: "10px",
                        3.5: "14px"
                    }
                },
                plugins: [],
                important: "#webcrumbs"
            }
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    </body>
</html>



