# Simulado-Senac-5

O intuito é fazer um simulado para treinar para a competição do Senac.

--- 

# A Estrutura:

- Consiste de uma aplicação monólita com apenas um banco de dados.


--- 


# Ferramentas utilizadas:

## Aplicativos: 
    
    - Visual Studio Code;
    
    - Insomnia;
    
    - Mysql Workbench;
    
    - Mysql Server;
    
    - Git;
    
    - Github;
    

## Tecnologias e bibliotecas:

```Na parte do backend foi usado:```


```
{
    "require": {
        "vlucas/phpdotenv": "^5.6",
        "firebase/php-jwt": "^7.0",
        "respect/validation": "^2.4"
    }
}


```

--- 


# Como rodar:

Primeiramente instale as ferramentas descritas anteriormente para que tudo funcione corretamente.
<p>

- Logo após, inicie o VS code, aperte CTRL + J para abrir o ```TERMINAL``` e com ele aberto clique no "+" e selecione 'Git bash' ou 'Bash', então, digite lá dentro:

```bash
git clone https://github.com/RicardoFM1/Simulado-Senac-5.git . 


```

--- 

Após isto, faça a mesma coisa de clicar em "+" porém selecione "Terminal" ou "CMD" e então digite:

```
cd backend/routes
php -S localhost:3000

```

- Fazendo isso você acaba de iniciar a API.
<p>

E por final abra seu Mysql Workbench, clique em 'Novo script' em 'FILE' e cole isto:

```sql
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema db_casamento
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema db_casamento
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `db_casamento` DEFAULT CHARACTER SET utf8 ;
USE `db_casamento` ;

-- -----------------------------------------------------
-- Table `db_casamento`.`usuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_casamento`.`usuario` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NOT NULL,
  `email` VARCHAR(45) NOT NULL,
  `cpf` VARCHAR(11) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `cargo` VARCHAR(45) NOT NULL DEFAULT 'ceremonialista',
  PRIMARY KEY (`id_usuario`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) VISIBLE,
  UNIQUE INDEX `cpf_UNIQUE` (`cpf` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_casamento`.`mesa`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_casamento`.`mesa` (
  `id_mesa` INT NOT NULL AUTO_INCREMENT,
  `capacidade` INT NOT NULL,
  `restricao` VARCHAR(45) NULL,
  PRIMARY KEY (`id_mesa`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_casamento`.`convidado`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_casamento`.`convidado` (
  `id_convidado` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NOT NULL,
  `sobrenome` VARCHAR(45) NOT NULL,
  `email` VARCHAR(45) NOT NULL,
  `cpf` VARCHAR(11) NOT NULL,
  `telefone` VARCHAR(45) NOT NULL,
  `categoria` VARCHAR(45) NOT NULL,
  `confirmacao` VARCHAR(45) NOT NULL DEFAULT 'não confirmado',
  `mesa_idmesa` INT NULL,
  PRIMARY KEY (`id_convidado`),
  UNIQUE INDEX `cpf_UNIQUE` (`cpf` ASC) VISIBLE,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) VISIBLE,
  INDEX `fk_convidado_mesa_idx` (`mesa_idmesa` ASC) VISIBLE,
  CONSTRAINT `fk_convidado_mesa`
    FOREIGN KEY (`mesa_idmesa`)
    REFERENCES `db_casamento`.`mesa` (`id_mesa`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_casamento`.`checkin`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_casamento`.`checkin` (
  `id_checkin` INT NOT NULL AUTO_INCREMENT,
  `usuario_idusuario` INT NOT NULL,
  `convidado_idconvidado` INT NOT NULL,
  `data_e_hora` TIMESTAMP NULL,
  PRIMARY KEY (`id_checkin`),
  UNIQUE INDEX `convidado_idconvidado_UNIQUE` (`convidado_idconvidado` ASC) VISIBLE,
  INDEX `fk_checkin_usuario_idx` (`usuario_idusuario` ASC) VISIBLE,
  CONSTRAINT `fk_checkin_usuario`
    FOREIGN KEY (`usuario_idusuario`)
    REFERENCES `db_casamento`.`usuario` (`id_usuario`)
    ON DELETE RESTRICT
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_checkin_convidado`
    FOREIGN KEY (`convidado_idconvidado`)
    REFERENCES `db_casamento`.`convidado` (`id_convidado`)
    ON DELETE RESTRICT
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_casamento`.`acompanhante`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_casamento`.`acompanhante` (
  `id_acompanhante` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NOT NULL,
  `sobrenome` VARCHAR(45) NOT NULL,
  `email` VARCHAR(45) NOT NULL,
  `cpf` VARCHAR(11) NOT NULL,
  `idade` INT NOT NULL,
  `convidado_idconvidado` INT NOT NULL,
  PRIMARY KEY (`id_acompanhante`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) VISIBLE,
  UNIQUE INDEX `cpf_UNIQUE` (`cpf` ASC) VISIBLE,
  INDEX `fk_acompanhante_convidado_idx` (`convidado_idconvidado` ASC) VISIBLE,
  CONSTRAINT `fk_acompanhante_convidado`
    FOREIGN KEY (`convidado_idconvidado`)
    REFERENCES `db_casamento`.`convidado` (`id_convidado`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
	
    
SET time_zone = '-03:00';

INSERT INTO usuario (nome, email, cpf, senha, cargo)
VALUES ('ricardo', 'ricardo@gmail.com', '05380295010', '$2a$12$rJGYwj2JeECDzziy9KZx1.Bc6K.3N3cNHUeFkPr2ue4qXSEvBkeKO', 'admin'),
('ricardo2', 'ricardo2@gmail.com', '61442391030', '$2a$12$rJGYwj2JeECDzziy9KZx1.Bc6K.3N3cNHUeFkPr2ue4qXSEvBkeKO', 'ceremonialista');

INSERT INTO mesa (capacidade, restricao)
VALUES (100, 'Lactose');

DELIMITER $$
CREATE PROCEDURE seed_convidados()
BEGIN
DECLARE i INT DEFAULT 1;
WHILE i <= 30 DO
INSERT INTO convidado (nome, sobrenome, email, cpf, telefone, categoria, confirmacao, mesa_idmesa)
VALUES(
	CONCAT('ricardo', i),
    CONCAT('fernandes', i),
    CONCAT('ricardo', i, '@gmail.com'),
    LPAD(i, 11 '0'),
    CONCAT('519999', LPAD(i, 4, '0')),
    IF(i % 2 = 0, 'parente', 'amigo'),
    IF(i % 2 = 0, 'confirmado', 'cancelado'),
    1
);

SET i = i+1;
END WHILE;

END $$
DELIMITER ;
CALL seed_convidados();



INSERT INTO checkin (usuario_idusuario, convidado_idconvidado, data_e_hora)
VALUES (1, 1, '2026-03-12');


INSERT INTO acompanhante (nome, sobrenome, email, cpf, idade, convidado_idconvidado)
VALUES ('Fernando', 'Fernandes', 'fernando@gmail.com', '05380295010' , 18, 1);




SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

```

- Selecione tudo e aperte CTRL + ENTER para criar SCHEMAS, SEEDERS e o necessário.
<p>

---

## .ENV

- Você pode perceber que nos arquivos tem um chamado '.env.example', isto é o exemplo de .env (chaves de ambiente) que você deve seguir, apenas mude os campos de valores para os valores do seu banco de dados que você criou localmente.

<p>

- A Chave secreta pode ser qualquer palavra, caractére que vai funcionar.


--- 


# Rotas:

O sistema consiste das seguintes rotas:


## Rota de Usuário:
<p>

/usuario - Métodos:
<p>

```GET```
<p>

```POST```

<p>

```PUT```

<p>

```DELETE```

<p>

- Nota-se que é necessário estar logado e ser um ADMIN para acessar essa rota.

<p>
Exemplo de JSON:

```json
"nome": "ricardo",
"email": "ricardo@gmail.com",
"cpf": "05380295010",
"senha": "12345678",
"cargo": "admin"

```



Contando também com login:

<p>


/usuario/login - Método: ```POST```

Exemplo de JSON:

```json
"email": "ricardo@gmail.com",
"senha": "12345678"

```

--- 


## Rota de Convidados:
<p>

/convidado - Métodos:
<p>

```GET```
<p>

```POST```

<p>

```PUT```

<p>

```DELETE```

<p>

- Nota-se que é necessário estar logado para acessar essa rota.

<p>

Exemplo de JSON:

```json
"nome": "ricardo",
"sobrenome": "Fernandes",
"email": "fernandes@gmail.com",
"cpf": "05380295010",
"telefone": "51999999",
"categoria": "parente",
"confirmacao": "cancelado",
"mesa_idmesa": 1 (Opcional)

```

<p>


--- 

## Rota de Mesas:
<p>

/mesa - Métodos:
<p>

```GET```
<p>

```POST```

<p>

```PUT```

<p>

```DELETE```

<p>

- Nota-se que é necessário estar logado para acessar essa rota.

<p>

Exemplo de JSON:

```json
"capacidade": 100,
"restricao": "Lactose" (Opcional)

```

---


## Rota de Checkins:
<p>

/checkin - Métodos:
<p>

```GET```
<p>

```POST```

<p>

```PUT```

<p>

```DELETE```

<p>

- Nota-se que é necessário estar logado para acessar essa rota.

<p>

Exemplo de JSON:

```json
"convidado_idconvidado": 1
```

---


## Rota de Acompanhantes:
<p>

/acompanhante - Métodos:
<p>

```GET```
<p>

```POST```

<p>

```PUT```

<p>

```DELETE```

<p>

- Nota-se que é necessário estar logado para acessar essa rota.

<p>

Exemplo de JSON:

```json
"nome": "ricardo",
"sobrenome": "Fernandes",
"email": "fernandes@gmail.com",
"cpf": "05380295010",
"idade": 19,
"convidado_idconvidado": 1 

```

--- 

## Rota de Retrieve:
<p>

/retrieve - Métodos:
<p>

```GET```
<p>

- Nota-se que é necessário estar logado para acessar essa rota.


## Rota de Dashboard:
<p>

/dashboard - Métodos:
<p>

```GET```
<p>

- Nota-se que é necessário estar logado e ser um ADMIN para acessar essa rota.
