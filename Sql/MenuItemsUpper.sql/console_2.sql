use pizza;
update ti_menus set menu_name =  UPPER(menu_name);
update ti_menus set subtract_stock = 0 where subtract_stock is null;
update ti_menus set minimum_qty = 1 where minimum_qty = 0;

select * from ti_menus;

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`)
value ("FETTUCCINI ALFREDO","",13.99,1);


Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ("VEAL PARAMIGAN","New York Veal. Includes spagehetti.",17.99,1,0);
Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ("BAKED ZITI","",12.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('VEAL CUTLET PLATE','Two veal patties topped with onions, peppers, mushrooms and brown gravy. Includes fries and spaghetti.',14.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('CHICKEN MARSALA','Sauteed in marsala wine with mushrooms, fresh garlic, lemon and gravy.  Includes spaghetti.',17.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('SPAGHETTI','',10.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('LASAGNA','Fresh lasagna pasta layered with our homemade sauce, fresh ground chuck and 3 Italian cheeses.',13.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('EGGPLANT PARMIGIANA','Includes spaghetti.',14.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('CHEESE RAVIOLI','',11.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('VEAL MARSALA','Saulteed in marsala wine with mushrooms, fresh garlic, lemon, and gravy. Includes spaghetti.',19.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('VEAL MARSALA','Saulteed in marsala wine with mushrooms, fresh garlic, lemon, and gravy. Includes spaghetti.',19.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Grouper','Includes flounder, shrimp and scallops
Served with fries',14.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Grouper','Includes flounder, shrimp and scallops
Served with fries',14.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Shrimp Parmigiana','Over spaghetti',17.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Shrimp Scampi','Sautéed in white wine, butter, lemon and fresh garlic over spaghetti.',18.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Spaghetti & Clam Sauce','White or red.',14.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Potato salad','',2.49,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Onion Rings','',4.29,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Greek Peppers','', 1.49,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Marinara Sauce','', .99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Basket of Garlic Bread','', 2.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Basket of Garlic Bread with melted cheese','''',  3.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Garlic Sticks','',  1.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Meatballs','',  4.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Brown Gravy','',  99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Fries','',  2.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Spaghetti','',  3.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Sausage','',  4.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Feta Cheese','',  1.99,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Side salad','', 3.99,1,0);
Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Dinner Salad','', 5.99,1,0);
Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
value ('Luigi''s Salad','Lettuc, tomatoes, salami, cheese, cucumbers, ham, onions and olives.', 9.49,1,0);

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
select 'Grilld Chicken Salad','', 10.99,1,0
union
select 'Chicken Caesar Salad','', 10.99,1,0
union
select 'Greek Salad','', 10.99,1,0
union
select 'Antipasto','Lettuce, tomatoes, salami, cheese, ham, pepperoni, greek peppers,
mozzarella cheese, olives, cucumbers, onions and anchovies.
Includes galic sticks', 11.99,1,0

Insert INTO ti_menus(`menu_name`,`menu_description`,`menu_price`,`menu_status`,`subtract_stock`)
select 'Big Bob Hamburger','Lettuce, tomatoes, onions, pickles, spear and chips', 7.99,1,0
union
select '6oz Hamburger','Lettuce, tomatoes, onions, pickles spear & chips', 5.99,1,0
union
select 'Ham & Cheese','Lettuce and tomatoes', 6.99,1,0
union
select 'Submarine','Ham, salami, cheese, onions, lettuce, tomatoes and Italian dressing', 7.99,1,0
union
select 'Club Sandwich','Ham or turkey on toasted rye bread.', 8.99,1,0
union
select 'Meatball Parmigiana','', 8.99,1,0
union
select 'Sausage Parmigiana','', 8.99,1,0
union
select 'Veal Parmigiana','', 8.99,1,0
union
select 'Chicken Parmigiana','', 8.99,1,0
union
select 'Turkey','Lettuce, tomates and cheese, served on white, wheat or rye bread and pickle spear.', 7.99,1,0
union
select 'Eggplant Parmigiana','', 7.99,1,0
union
select 'Eddie''s Tuna Salad Sandwich','On white, wheat or rye bread with pickle spear and chips.', 8.99,1,0
union
select 'Chicken on a bun','Lettuce, tomatoes, pickle spear & chips', 5.99,1,0
union
select 'Grilled Cheese','Pickle spear and chips.', 4.99,1,0
union
select 'Fish Sandwich on a bun','Lettuce, tomatoes, pickle spear & chips.', 8.99,1,0

