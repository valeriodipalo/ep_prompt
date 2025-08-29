/**
 * Professional Color Presets
 * Curated combinations for common salon requests
 */

import { ColorPreset } from '../types/color';

export const colorPresets: ColorPreset[] = [
  // CLASSIC SINGLE-TONE PRESETS
  {
    id: 'classic-brunette',
    label: 'Classic Brunette',
    description: 'Timeless medium brown',
    choice: {
      type: 'single-tone',
      singleTone: {
        colorId: 'medium-brown'
      }
    }
  },
  {
    id: 'golden-goddess',
    label: 'Golden Goddess',
    description: 'Warm golden blonde',
    choice: {
      type: 'single-tone',
      singleTone: {
        colorId: 'golden-blonde'
      }
    }
  },
  {
    id: 'midnight-black',
    label: 'Midnight Black',
    description: 'Deep jet black',
    choice: {
      type: 'single-tone',
      singleTone: {
        colorId: 'jet-black'
      }
    }
  },
  {
    id: 'auburn-beauty',
    label: 'Auburn Beauty',
    description: 'Natural auburn red',
    choice: {
      type: 'single-tone',
      singleTone: {
        colorId: 'auburn'
      }
    }
  },

  // BALAYAGE PRESETS
  {
    id: 'honey-balayage',
    label: 'Honey Balayage',
    description: 'Brown base with honey highlights',
    choice: {
      type: 'accented',
      accented: {
        baseColorId: 'medium-brown',
        accentColorId: 'honey-blonde',
        technique: 'balayage',
        intensity: 'standard',
        placement: 'face-framing',
        blend: 'soft'
      }
    }
  },
  {
    id: 'caramel-balayage',
    label: 'Caramel Balayage',
    description: 'Dark brown with caramel highlights',
    choice: {
      type: 'accented',
      accented: {
        baseColorId: 'dark-brown',
        accentColorId: 'golden-blonde',
        technique: 'balayage',
        intensity: 'standard',
        placement: 'mid-ends',
        blend: 'soft'
      }
    }
  },
  {
    id: 'ash-balayage',
    label: 'Ash Balayage',
    description: 'Cool-toned balayage',
    choice: {
      type: 'accented',
      accented: {
        baseColorId: 'light-brown',
        accentColorId: 'ash-blonde',
        technique: 'balayage',
        intensity: 'subtle',
        placement: 'overall',
        blend: 'soft'
      }
    }
  },

  // OMBRE PRESETS
  {
    id: 'chocolate-ombre',
    label: 'Chocolate Ombré',
    description: 'Dark to light brown gradient',
    choice: {
      type: 'accented',
      accented: {
        baseColorId: 'chocolate-brown',
        accentColorId: 'light-brown',
        technique: 'ombre',
        intensity: 'standard',
        placement: 'mid-ends',
        blend: 'defined'
      }
    }
  },
  {
    id: 'sunset-ombre',
    label: 'Sunset Ombré',
    description: 'Brown to golden blonde',
    choice: {
      type: 'accented',
      accented: {
        baseColorId: 'espresso',
        accentColorId: 'golden-blonde',
        technique: 'ombre',
        intensity: 'bold',
        placement: 'mid-ends',
        blend: 'defined'
      }
    }
  },
  {
    id: 'rose-ombre',
    label: 'Rose Ombré',
    description: 'Brown to rose gold gradient',
    choice: {
      type: 'accented',
      accented: {
        baseColorId: 'dark-brown',
        accentColorId: 'rose-gold',
        technique: 'ombre',
        intensity: 'bold',
        placement: 'tips',
        blend: 'defined'
      }
    }
  },

  // HIGHLIGHT PRESETS
  {
    id: 'platinum-highlights',
    label: 'Platinum Highlights',
    description: 'Bold platinum on dark base',
    choice: {
      type: 'accented',
      accented: {
        baseColorId: 'dark-brown',
        accentColorId: 'platinum-blonde',
        technique: 'highlights',
        intensity: 'bold',
        placement: 'overall',
        blend: 'defined'
      }
    }
  },
  {
    id: 'copper-highlights',
    label: 'Copper Highlights',
    description: 'Warm copper on brown base',
    choice: {
      type: 'accented',
      accented: {
        baseColorId: 'chestnut',
        accentColorId: 'copper',
        technique: 'highlights',
        intensity: 'standard',
        placement: 'face-framing',
        blend: 'soft'
      }
    }
  },

  // LOWLIGHT PRESETS
  {
    id: 'chocolate-lowlights',
    label: 'Chocolate Lowlights',
    description: 'Rich depth with chocolate',
    choice: {
      type: 'accented',
      accented: {
        baseColorId: 'light-brown',
        accentColorId: 'chocolate-brown',
        technique: 'lowlights',
        intensity: 'subtle',
        placement: 'overall',
        blend: 'soft'
      }
    }
  },
  {
    id: 'mahogany-lowlights',
    label: 'Mahogany Lowlights',
    description: 'Red undertones for warmth',
    choice: {
      type: 'accented',
      accented: {
        baseColorId: 'medium-brown',
        accentColorId: 'mahogany',
        technique: 'lowlights',
        intensity: 'standard',
        placement: 'overall',
        blend: 'soft'
      }
    }
  },

  // CREATIVE PRESETS
  {
    id: 'unicorn-tips',
    label: 'Unicorn Tips',
    description: 'Pastel rainbow tips',
    choice: {
      type: 'accented',
      accented: {
        baseColorId: 'platinum-blonde',
        accentColorId: 'pastel-pink',
        technique: 'ombre',
        intensity: 'bold',
        placement: 'tips',
        blend: 'defined'
      }
    }
  },
  {
    id: 'mermaid-hair',
    label: 'Mermaid Hair',
    description: 'Ocean-inspired blues and greens',
    choice: {
      type: 'accented',
      accented: {
        baseColorId: 'platinum-blonde',
        accentColorId: 'electric-blue',
        technique: 'balayage',
        intensity: 'bold',
        placement: 'mid-ends',
        blend: 'soft'
      }
    }
  },
  {
    id: 'galaxy-hair',
    label: 'Galaxy Hair',
    description: 'Deep space purples',
    choice: {
      type: 'accented',
      accented: {
        baseColorId: 'jet-black',
        accentColorId: 'royal-purple',
        technique: 'highlights',
        intensity: 'bold',
        placement: 'overall',
        blend: 'defined'
      }
    }
  },

  // PROFESSIONAL PRESETS
  {
    id: 'executive-brunette',
    label: 'Executive Brunette',
    description: 'Sophisticated workplace-appropriate brown',
    choice: {
      type: 'single-tone',
      singleTone: {
        colorId: 'espresso'
      }
    }
  },
  {
    id: 'boardroom-blonde',
    label: 'Boardroom Blonde',
    description: 'Professional champagne blonde',
    choice: {
      type: 'single-tone',
      singleTone: {
        colorId: 'champagne-blonde'
      }
    }
  },
  {
    id: 'distinguished-silver',
    label: 'Distinguished Silver',
    description: 'Elegant silver gray',
    choice: {
      type: 'single-tone',
      singleTone: {
        colorId: 'silver-gray'
      }
    }
  }
];

export default colorPresets;
