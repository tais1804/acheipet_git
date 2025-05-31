
<?php

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = array();
}

$total_itens_carrinho = 0;
foreach ($_SESSION['carrinho'] as $produto) {
    $total_itens_carrinho += $produto['quantidade'];
}
?>
<header class="py-4 px-6 flex items-center justify-between menu-topo">
                    <div class="flex items-center">
                        <a href="index.php" class="flex items-center">
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
                        <button onclick="window.location.href='carrinho.php'" class="text-gray-700 hover:text-gray-900 transition-colors carrinho">
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center cont-itens"><?php echo $total_itens_carrinho; ?></span>
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
                        <div class="relative, foto-home dropdown">
                            <img src="../img/foto-user.png" data-bs-toggle="dropdown" alt="Profile" class=" dropdown-toggle h-10 w-10 rounded-full object-cover border-2 border-red-500 hover:border-red-600 transition-all transform hover:scale-105"/>
                            <ul class="dropdown-menu">
                                <li class="dropdown-item"> <p><?php echo $usuario["nome"]; ?></p></li>
                                <li class="dropdown-item"><a href="meus_pets.php">Meus pets</a></li>
                                <li class="dropdown-item">
                                    <form method="post" action="perfil_usuario.php">
                                        <input type="submit" name="deslogar" value="Sair do sistema">
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </header>