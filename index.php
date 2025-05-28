<?php
    include "dados_usuario.php";
?>
<!--<!DOCTYPE html>
<html>
<head>
    <title>Achei Pet</title>
</head>
<body>
    <h1>Bem-vindo ao Achei Pet</h1>
    <p><a href="cadastrar_usuario.php">Cadastrar Usuário</a></p>
    <p><a href="listar_usuarios.php">Listar Usuários</a></p>
    <p><a href="cadastrar_pet.php">Cadastrar Pet</a></p>
    <p><a href="listar_pets.php">Listar Pets</a></p>
    <p><a href="editar_pet.php?id=1">Editar Pet </a></p>
    <p><a href="excluir_pet.php?id=1">Excluir Pet </a></p>
    <p><a href="adotar_pet.php?id=1">Adotar Pet </a></p>
    <p><a href="mapa_veterinarios.php">Buscar Veterinários</a></p>
    <p><a href="loja_virtual.php">Loja Virtual</a></p>
    <p><a href="blog.php">Blog</a></p>
    <p><a href="cadastrar_pet_perdido.php">Cadastrar Animal Perdido</a></p> </body>
</html>
-->

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>My Webcrumbs Plugin</title>
        <style>
            @import url(https://fonts.googleapis.com/css2?family=Lato&display=swap);
            @import url(https://fonts.googleapis.com/css2?family=Open+Sans&display=swap);
        </style>
        <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
        
    </head>
    <body class="bg_body_white_teste">
        <div id="webcrumbs">
            <div class="relative w-full min-h-screen bg-white">
                <!--<header class="py-4 px-6 flex items-center justify-between menu-topo">
                    <div class="flex items-center">
                        <a href="https://webcrumbs.cloud/placeholder" class="flex items-center">
                            <img
                                src="../img/logo.png"
                                alt="AcheiPet"
                                class="h-10 mr-2 hover:opacity-80 transition-opacity"
                            />
                        </a>
                        <nav class="ml-6 hidden md:flex">
                            <ul class="flex space-x-6">
                                <li>
                                    <a
                                        href="listar_pets.php"
                                        class="text-red-500 hover:text-red-600 transition-colors"
                                        >Adotar</a
                                    >
                                </li>
                                <li>
                                    <a
                                        href="cadastrar_pet.php"
                                        class="text-red-500 hover:text-red-600 transition-colors"
                                        >Doar</a
                                    >
                                </li>
                                <li>
                                    <a
                                        href="cadastrar_pet_perdido.php"
                                        class="text-red-500 hover:text-red-600 transition-colors"
                                        >Perdi meu pet</a
                                    >
                                </li>
                                <li>
                                    <a href="loja_virtual.php" class="text-red-500 hover:text-red-600 transition-colors">PetShop</a>
                                </li>
                                <li>
                                    <a href="mapa_veterinarios.php" class="text-red-500 hover:text-red-600 transition-colors">Veterinários</a>
                                </li>
                                <li>
                                    <a href="blog.php" class="text-red-500 hover:text-red-600 transition-colors">Blog</a>
                                </li>

                            </ul>
                        </nav>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button class="text-gray-700 hover:text-gray-900 transition-colors">
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center cont-itens">2</span>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="w-6 h-6"
                            >
                                <path
                                    d="M2.25 2.25a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 00-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 000-1.5H5.378A2.25 2.25 0 017.5 15h11.218a.75.75 0 00.674-.421 60.358 60.358 0 002.96-7.228.75.75 0 00-.525-.965A60.864 60.864 0 005.68 4.509l-.232-.867A1.875 1.875 0 003.636 2.25H2.25zM3.75 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM16.5 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z"
                                ></path>
                            </svg>
                        </button>
                        <button class="text-gray-700 hover:text-gray-900 transition-colors">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="w-6 h-6"
                            >
                                <path
                                    d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"
                                ></path>
                            </svg>
                        </button>
                        <div class="relative, foto-home">
                            <img src="../img/foto-user.png" alt="Profile" class="h-10 w-10 rounded-full object-cover border-2 border-red-500 hover:border-red-600 transition-all transform hover:scale-105"/>
                            
                        </div>
                    </div>
                </header>-->
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
                            <div class="space-y-6 cards-opcoes">
                                
                                
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
                                class="max-h-[500px] object-cover rounded-lg"
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

<?php
    include "dados_usuario.php";
?>
<!--<!DOCTYPE html>
<html>
<head>
    <title>Achei Pet</title>
</head>
<body>
    <h1>Bem-vindo ao Achei Pet</h1>
    <p><a href="cadastrar_usuario.php">Cadastrar Usuário</a></p>
    <p><a href="listar_usuarios.php">Listar Usuários</a></p>
    <p><a href="cadastrar_pet.php">Cadastrar Pet</a></p>
    <p><a href="listar_pets.php">Listar Pets</a></p>
    <p><a href="editar_pet.php?id=1">Editar Pet </a></p>
    <p><a href="excluir_pet.php?id=1">Excluir Pet </a></p>
    <p><a href="adotar_pet.php?id=1">Adotar Pet </a></p>
    <p><a href="mapa_veterinarios.php">Buscar Veterinários</a></p>
    <p><a href="loja_virtual.php">Loja Virtual</a></p>
    <p><a href="blog.php">Blog</a></p>
    <p><a href="cadastrar_pet_perdido.php">Cadastrar Animal Perdido</a></p> </body>
</html>
-->

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>My Webcrumbs Plugin</title>
        <style>
            @import url(https://fonts.googleapis.com/css2?family=Lato&display=swap);
            @import url(https://fonts.googleapis.com/css2?family=Open+Sans&display=swap);
        </style>
        <link rel="stylesheet" type="text/css" href="css/estilo-achei-pet.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
        
    </head>
    <body>
        <div id="webcrumbs">
            <div class="relative w-full min-h-screen bg-white">
                <!--<header class="py-4 px-6 flex items-center justify-between menu-topo">
                    <div class="flex items-center">
                        <a href="https://webcrumbs.cloud/placeholder" class="flex items-center">
                            <img
                                src="../img/logo.png"
                                alt="AcheiPet"
                                class="h-10 mr-2 hover:opacity-80 transition-opacity"
                            />
                        </a>
                        <nav class="ml-6 hidden md:flex">
                            <ul class="flex space-x-6">
                                <li>
                                    <a
                                        href="listar_pets.php"
                                        class="text-red-500 hover:text-red-600 transition-colors"
                                        >Adotar</a
                                    >
                                </li>
                                <li>
                                    <a
                                        href="cadastrar_pet.php"
                                        class="text-red-500 hover:text-red-600 transition-colors"
                                        >Doar</a
                                    >
                                </li>
                                <li>
                                    <a
                                        href="cadastrar_pet_perdido.php"
                                        class="text-red-500 hover:text-red-600 transition-colors"
                                        >Perdi meu pet</a
                                    >
                                </li>
                                <li>
                                    <a href="loja_virtual.php" class="text-red-500 hover:text-red-600 transition-colors">PetShop</a>
                                </li>
                                <li>
                                    <a href="mapa_veterinarios.php" class="text-red-500 hover:text-red-600 transition-colors">Veterinários</a>
                                </li>
                                <li>
                                    <a href="blog.php" class="text-red-500 hover:text-red-600 transition-colors">Blog</a>
                                </li>

                            </ul>
                        </nav>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button class="text-gray-700 hover:text-gray-900 transition-colors">
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center cont-itens">2</span>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="w-6 h-6"
                            >
                                <path
                                    d="M2.25 2.25a.75.75 0 000 1.5h1.386c.17 0 .318.114.362.278l2.558 9.592a3.752 3.752 0 00-2.806 3.63c0 .414.336.75.75.75h15.75a.75.75 0 000-1.5H5.378A2.25 2.25 0 017.5 15h11.218a.75.75 0 00.674-.421 60.358 60.358 0 002.96-7.228.75.75 0 00-.525-.965A60.864 60.864 0 005.68 4.509l-.232-.867A1.875 1.875 0 003.636 2.25H2.25zM3.75 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM16.5 20.25a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0z"
                                ></path>
                            </svg>
                        </button>
                        <button class="text-gray-700 hover:text-gray-900 transition-colors">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="w-6 h-6"
                            >
                                <path
                                    d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"
                                ></path>
                            </svg>
                        </button>
                        <div class="relative, foto-home">
                            <img src="../img/foto-user.png" alt="Profile" class="h-10 w-10 rounded-full object-cover border-2 border-red-500 hover:border-red-600 transition-all transform hover:scale-105"/>
                            
                        </div>
                    </div>
                </header>-->
                <?php
                    include "header.php";
                ?>
                <main class="relative w-full bg-red-500 testes">
                    <div class="container mx-auto px-4 py-10 flex flex-col lg:flex-row">
                        <div class="lg:w-1/2 z-10 pr-4 opcoes-home">
                            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">O que deseja fazer hoje?</h1>
                            <p class="text-white mb-10">
                                Aqui você pode encontrar todo tipo de informação sobre adoção, doação e informações de
                                animais desaparecidos.
                            </p>
                            <div class="space-y-6 cards-opcoes">
                                
                                
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
                                class="max-h-[500px] object-cover rounded-lg"
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
