    const { PrismaClient } = require('@prisma/client');
    const prisma = new PrismaClient();

    async function main() {
        // Create a Role
        const role = await prisma.role.create({
            data: {
                roleName: 'Admin',
            },
        });
        console.log('Created Role:', role);

        // Create a User
        const user = await prisma.user.create({
            data: {
                name: 'John Doe',
                email: 'john.doe@example.com',
                password: 'securepassword',
                roleId: role.id, // Associate with the created role
            },
        });
        console.log('Created User:', user);

        // Create a Product
        const product = await prisma.product.create({
            data: {
                name: 'Example Product',
                description: 'A sample product',
                price: 19.99,
                stock: 100,
            },
        });
        console.log('Created Product:', product);

        // Create an Order
        const order = await prisma.order.create({
            data: {
                userId: user.id, // Associate with the created user
                total: 59.99,
            },
        });
        console.log('Created Order:', order);

        // Fetch all Users
        const users = await prisma.user.findMany({
            include: { Role: true, Orders: true },
        });
        console.log('Users:', users);
    }

    main()
        .catch((e) => {
            console.error(e);
            process.exit(1);
        })
        .finally(async () => {
            await prisma.$disconnect();
        });
