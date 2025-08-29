/**
 * Professional Hair Color Palette
 * Salon-accurate colors with proper categorization
 */

import { HairColor } from '../types/color';

export const defaultPalette: HairColor[] = [
  // BLACK FAMILY
  {
    id: 'jet-black',
    name: 'Jet Black',
    hex: '#0A0A0A',
    family: 'black',
    isPremium: false,
    description: 'Deep, rich black with blue undertones',
    tags: ['natural', 'classic', 'professional']
  },
  {
    id: 'soft-black',
    name: 'Soft Black',
    hex: '#1C1C1C',
    family: 'black',
    isPremium: false,
    description: 'Softer black with subtle warmth',
    tags: ['natural', 'versatile']
  },
  {
    id: 'blue-black',
    name: 'Blue Black',
    hex: '#0F0F23',
    family: 'black',
    isPremium: true,
    description: 'Black with cool blue undertones',
    tags: ['dramatic', 'cool-toned']
  },

  // BRUNETTE FAMILY
  {
    id: 'dark-brown',
    name: 'Dark Brown',
    hex: '#2F1B14',
    family: 'brunette',
    isPremium: false,
    description: 'Rich, deep brown',
    tags: ['natural', 'classic', 'warm']
  },
  {
    id: 'medium-brown',
    name: 'Medium Brown',
    hex: '#5D4037',
    family: 'brunette',
    isPremium: false,
    description: 'Versatile medium brown',
    tags: ['natural', 'versatile', 'warm']
  },
  {
    id: 'light-brown',
    name: 'Light Brown',
    hex: '#8D6E63',
    family: 'brunette',
    isPremium: false,
    description: 'Soft, light brown',
    tags: ['natural', 'light', 'warm']
  },
  {
    id: 'chocolate-brown',
    name: 'Chocolate Brown',
    hex: '#3E2723',
    family: 'brunette',
    isPremium: true,
    description: 'Rich chocolate with warm undertones',
    tags: ['luxurious', 'warm', 'rich']
  },
  {
    id: 'espresso',
    name: 'Espresso',
    hex: '#4A2C2A',
    family: 'brunette',
    isPremium: true,
    description: 'Deep coffee brown',
    tags: ['sophisticated', 'rich', 'warm']
  },
  {
    id: 'chestnut',
    name: 'Chestnut',
    hex: '#954535',
    family: 'brunette',
    isPremium: true,
    description: 'Warm reddish-brown',
    tags: ['warm', 'rich', 'dimensional']
  },

  // BLONDE FAMILY
  {
    id: 'golden-blonde',
    name: 'Golden Blonde',
    hex: '#DAA520',
    family: 'blonde',
    isPremium: false,
    description: 'Classic golden blonde',
    tags: ['natural', 'warm', 'classic']
  },
  {
    id: 'honey-blonde',
    name: 'Honey Blonde',
    hex: '#D4A574',
    family: 'blonde',
    isPremium: true,
    description: 'Warm honey-toned blonde',
    tags: ['warm', 'natural', 'luxurious']
  },
  {
    id: 'ash-blonde',
    name: 'Ash Blonde',
    hex: '#C4A484',
    family: 'blonde',
    isPremium: true,
    description: 'Cool-toned ash blonde',
    tags: ['cool', 'modern', 'sophisticated']
  },
  {
    id: 'strawberry-blonde',
    name: 'Strawberry Blonde',
    hex: '#C07F7F',
    family: 'blonde',
    isPremium: true,
    description: 'Blonde with red undertones',
    tags: ['unique', 'warm', 'dimensional']
  },
  {
    id: 'champagne-blonde',
    name: 'Champagne Blonde',
    hex: '#F7E7CE',
    family: 'blonde',
    isPremium: true,
    description: 'Light, elegant blonde',
    tags: ['light', 'elegant', 'sophisticated']
  },
  {
    id: 'butter-blonde',
    name: 'Butter Blonde',
    hex: '#F4E4BC',
    family: 'blonde',
    isPremium: true,
    description: 'Soft, buttery blonde',
    tags: ['soft', 'warm', 'natural']
  },

  // RED FAMILY
  {
    id: 'auburn',
    name: 'Auburn',
    hex: '#A52A2A',
    family: 'red',
    isPremium: false,
    description: 'Classic auburn red',
    tags: ['natural', 'warm', 'classic']
  },
  {
    id: 'copper',
    name: 'Copper',
    hex: '#B87333',
    family: 'red',
    isPremium: true,
    description: 'Bright copper red',
    tags: ['vibrant', 'warm', 'bold']
  },
  {
    id: 'mahogany',
    name: 'Mahogany',
    hex: '#722F37',
    family: 'red',
    isPremium: true,
    description: 'Deep red-brown',
    tags: ['rich', 'sophisticated', 'warm']
  },
  {
    id: 'burgundy',
    name: 'Burgundy',
    hex: '#800020',
    family: 'red',
    isPremium: true,
    description: 'Deep wine red',
    tags: ['dramatic', 'rich', 'bold']
  },
  {
    id: 'cherry-red',
    name: 'Cherry Red',
    hex: '#DE3163',
    family: 'red',
    isPremium: true,
    description: 'Vibrant cherry red',
    tags: ['bold', 'vibrant', 'statement']
  },

  // GRAY FAMILY
  {
    id: 'salt-pepper',
    name: 'Salt & Pepper',
    hex: '#808080',
    family: 'gray',
    isPremium: false,
    description: 'Natural gray blend',
    tags: ['natural', 'mature', 'distinguished']
  },
  {
    id: 'silver-gray',
    name: 'Silver Gray',
    hex: '#C0C0C0',
    family: 'gray',
    isPremium: true,
    description: 'Elegant silver gray',
    tags: ['sophisticated', 'modern', 'cool']
  },
  {
    id: 'charcoal-gray',
    name: 'Charcoal Gray',
    hex: '#36454F',
    family: 'gray',
    isPremium: true,
    description: 'Deep charcoal gray',
    tags: ['modern', 'sophisticated', 'cool']
  },

  // PLATINUM FAMILY
  {
    id: 'platinum-blonde',
    name: 'Platinum Blonde',
    hex: '#E5E4E2',
    family: 'platinum',
    isPremium: true,
    description: 'Ultra-light platinum',
    tags: ['dramatic', 'high-maintenance', 'bold']
  },
  {
    id: 'ice-blonde',
    name: 'Ice Blonde',
    hex: '#F8F8FF',
    family: 'platinum',
    isPremium: true,
    description: 'Cool platinum with icy undertones',
    tags: ['cool', 'dramatic', 'modern']
  },

  // FASHION COLORS
  {
    id: 'rose-gold',
    name: 'Rose Gold',
    hex: '#E8B4B8',
    family: 'fashion',
    isPremium: true,
    description: 'Trendy rose gold',
    tags: ['trendy', 'feminine', 'modern']
  },
  {
    id: 'smoky-mauve',
    name: 'Smoky Mauve',
    hex: '#915F6D',
    family: 'fashion',
    isPremium: true,
    description: 'Sophisticated mauve',
    tags: ['unique', 'sophisticated', 'cool']
  },

  // PASTEL COLORS
  {
    id: 'pastel-pink',
    name: 'Pastel Pink',
    hex: '#FFB6C1',
    family: 'pastel',
    isPremium: true,
    description: 'Soft pastel pink',
    tags: ['playful', 'feminine', 'creative']
  },
  {
    id: 'lavender',
    name: 'Lavender',
    hex: '#E6E6FA',
    family: 'pastel',
    isPremium: true,
    description: 'Soft lavender purple',
    tags: ['dreamy', 'unique', 'cool']
  },
  {
    id: 'mint-green',
    name: 'Mint Green',
    hex: '#98FB98',
    family: 'pastel',
    isPremium: true,
    description: 'Fresh mint green',
    tags: ['unique', 'fresh', 'creative']
  },

  // VIBRANT COLORS
  {
    id: 'electric-blue',
    name: 'Electric Blue',
    hex: '#7DF9FF',
    family: 'vibrant',
    isPremium: true,
    description: 'Bold electric blue',
    tags: ['bold', 'creative', 'statement']
  },
  {
    id: 'neon-green',
    name: 'Neon Green',
    hex: '#39FF14',
    family: 'vibrant',
    isPremium: true,
    description: 'Bright neon green',
    tags: ['bold', 'creative', 'statement']
  },
  {
    id: 'hot-pink',
    name: 'Hot Pink',
    hex: '#FF69B4',
    family: 'vibrant',
    isPremium: true,
    description: 'Vibrant hot pink',
    tags: ['bold', 'fun', 'statement']
  },
  {
    id: 'royal-purple',
    name: 'Royal Purple',
    hex: '#7851A9',
    family: 'vibrant',
    isPremium: true,
    description: 'Rich royal purple',
    tags: ['bold', 'regal', 'statement']
  }
];

export default defaultPalette;
