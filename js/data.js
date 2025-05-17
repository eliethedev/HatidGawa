
// Mock data for the application
const mockData = {
    users: [
        {
            id: 1,
            name: 'Maria Santos',
            username: 'maria_s',
            email: 'maria@example.com',
            phone: '09123456789',
            address: 'Brgy. San Antonio, Quezon City',
            isVerified: true,
            rating: 4.8,
            completedTasks: 24,
            skills: ['Delivery', 'Cleaning', 'Errands'],
            profilePic: 'https://randomuser.me/api/portraits/women/12.jpg'
        },
        {
            id: 2,
            name: 'Juan Dela Cruz',
            username: 'juan_dc',
            email: 'juan@example.com',
            phone: '09187654321',
            address: 'Brgy. San Antonio, Quezon City',
            isVerified: true,
            rating: 4.9,
            completedTasks: 36,
            skills: ['Repairs', 'Delivery', 'Tech Help'],
            profilePic: 'https://randomuser.me/api/portraits/men/32.jpg'
        },
        {
            id: 3,
            name: 'Ana Reyes',
            username: 'ana_r',
            email: 'ana@example.com',
            phone: '09198765432',
            address: 'Brgy. San Antonio, Quezon City',
            isVerified: false,
            rating: 4.5,
            completedTasks: 8,
            skills: ['Tutoring', 'Errands'],
            profilePic: 'https://randomuser.me/api/portraits/women/45.jpg'
        },
        {
            id: 4,
            name: 'Carlos Mendoza',
            username: 'carlos_m',
            email: 'carlos@example.com',
            phone: '09156789012',
            address: 'Brgy. San Antonio, Quezon City',
            isVerified: true,
            rating: 4.7,
            completedTasks: 19,
            skills: ['Delivery', 'Tech Help', 'Errands'],
            profilePic: 'https://randomuser.me/api/portraits/men/67.jpg'
        }
    ],
    tasks: [
        {
            id: 1,
            title: 'Grocery Delivery',
            description: 'Need help picking up groceries from the nearby supermarket and delivering to my home.',
            category: 'Delivery',
            payment: 200,
            urgency: 'Normal',
            location: 'Home',
            address: 'Brgy. San Antonio, Quezon City',
            status: 'Pending',
            createdAt: '2023-06-15T10:30:00',
            requesterId: 1,
            helperId: null,
            isPublicSafeZone: false
        },
        {
            id: 2,
            title: 'Fix Leaking Faucet',
            description: 'Need someone with plumbing experience to fix a leaking faucet in my kitchen.',
            category: 'Repairs',
            payment: 350,
            urgency: 'Urgent',
            location: 'Home',
            address: 'Brgy. San Antonio, Quezon City',
            status: 'Accepted',
            createdAt: '2023-06-14T15:45:00',
            requesterId: 3,
            helperId: 2,
            isPublicSafeZone: false,
            magicWord: 'BLUESKY'
        },
        {
            id: 3,
            title: 'Math Tutoring Session',
            description: 'Looking for a tutor to help with college algebra for 2 hours.',
            category: 'Tutoring',
            payment: 500,
            urgency: 'Normal',
            location: 'Safe Zone',
            safeZone: 'Barangay Hall',
            status: 'Completed',
            createdAt: '2023-06-10T13:00:00',
            requesterId: 2,
            helperId: 3,
            isPublicSafeZone: true,
            rating: 5
        },
        {
            id: 4,
            title: 'Smartphone Setup Help',
            description: 'Need assistance setting up a new smartphone for an elderly person, including basic apps and contacts.',
            category: 'Tech Help',
            payment: 300,
            urgency: 'Normal',
            location: 'Safe Zone',
            safeZone: 'Community Center',
            status: 'Waiting for Review',
            createdAt: '2023-06-13T09:15:00',
            requesterId: 4,
            helperId: null,
            isPublicSafeZone: true,
            applicants: [1, 2]
        },
        {
            id: 5,
            title: 'House Cleaning',
            description: 'Need help cleaning a 2-bedroom apartment, including bathroom and kitchen.',
            category: 'Cleaning',
            payment: 600,
            urgency: 'Urgent',
            location: 'Home',
            address: 'Brgy. San Antonio, Quezon City',
            status: 'Pending',
            createdAt: '2023-06-15T08:00:00',
            requesterId: 2,
            helperId: null,
            isPublicSafeZone: false
        }
    ],
    notifications: [
        {
            id: 1,
            userId: 1,
            title: 'New Task Application',
            message: 'Juan Dela Cruz has applied to your "Grocery Delivery" task.',
            time: '10 minutes ago',
            isRead: false,
            type: 'application'
        },
        {
            id: 2,
            userId: 1,
            title: 'Task Completed',
            message: 'Your "Math Tutoring Session" task has been marked as completed.',
            time: '2 days ago',
            isRead: true,
            type: 'completion'
        },
        {
            id: 3,
            userId: 1,
            title: 'New Safe Zone Added',
            message: 'A new Safe Zone has been added near your location: Community Library.',
            time: '3 days ago',
            isRead: true,
            type: 'system'
        }
    ],
    safeZones: [
        {
            id: 1,
            name: 'Barangay Hall',
            address: 'Main St., Brgy. San Antonio, Quezon City',
            isActive: true
        },
        {
            id: 2,
            name: 'Community Center',
            address: 'Park Ave., Brgy. San Antonio, Quezon City',
            isActive: true
        },
        {
            id: 3,
            name: 'Public Library',
            address: 'School Rd., Brgy. San Antonio, Quezon City',
            isActive: true
        }
    ],
    reviews: [
        {
            id: 1,
            taskId: 3,
            reviewerId: 2,
            revieweeId: 3,
            rating: 5,
            comment: 'Ana was very patient and explained the concepts clearly. Highly recommended!',
            createdAt: '2023-06-10T15:30:00'
        },
        {
            id: 2,
            taskId: 3,
            reviewerId: 3,
            revieweeId: 2,
            rating: 5,
            comment: 'Juan was very attentive and asked good questions. A pleasure to work with!',
            createdAt: '2023-06-10T15:35:00'
        }
    ]
};
