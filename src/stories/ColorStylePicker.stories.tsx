/**
 * Storybook Stories for ColorStylePicker
 * Interactive playground for testing color selection
 */

import React from 'react';
import type { Meta, StoryObj } from '@storybook/react';
import { ColorStylePicker } from '../components/ColorStylePicker';
import { ColorChoice } from '../types/color';
import defaultPalette from '../data/colors';

const meta: Meta<typeof ColorStylePicker> = {
  title: 'ColorStylePicker/Playground',
  component: ColorStylePicker,
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component: 'Professional hair color selection with single-tone and accented options'
      }
    }
  },
  argTypes: {
    value: {
      control: false,
      description: 'Current color choice value'
    },
    onChange: {
      action: 'color-changed',
      description: 'Callback when color selection changes'
    },
    palette: {
      control: false,
      description: 'Available color palette'
    },
    lockedIds: {
      control: 'check',
      options: defaultPalette.filter(c => c.isPremium).map(c => c.id),
      description: 'Color IDs that should show as locked (premium)'
    }
  },
  decorators: [
    (Story) => (
      <div style={{ maxWidth: '600px', padding: '20px' }}>
        <Story />
      </div>
    )
  ]
};

export default meta;
type Story = StoryObj<typeof ColorStylePicker>;

// Default story with single-tone selection
export const SingleTone: Story = {
  args: {
    value: {
      type: 'single-tone',
      singleTone: {
        colorId: 'medium-brown'
      }
    },
    palette: defaultPalette,
    lockedIds: []
  },
  render: function SingleToneStory(args) {
    const [value, setValue] = React.useState<ColorChoice>(args.value);
    
    return (
      <div>
        <ColorStylePicker
          {...args}
          value={value}
          onChange={setValue}
        />
        
        {/* Debug output */}
        <div className="mt-6 p-4 bg-gray-100 rounded-lg">
          <h4 className="font-medium mb-2">Current Selection:</h4>
          <pre className="text-sm text-gray-700 whitespace-pre-wrap">
            {JSON.stringify(value, null, 2)}
          </pre>
        </div>
      </div>
    );
  }
};

// Accented color story
export const Accented: Story = {
  args: {
    value: {
      type: 'accented',
      accented: {
        baseColorId: 'dark-brown',
        accentColorId: 'honey-blonde',
        technique: 'balayage',
        intensity: 'standard',
        placement: 'face-framing',
        blend: 'soft'
      }
    },
    palette: defaultPalette,
    lockedIds: []
  },
  render: function AccentedStory(args) {
    const [value, setValue] = React.useState<ColorChoice>(args.value);
    
    return (
      <div>
        <ColorStylePicker
          {...args}
          value={value}
          onChange={setValue}
        />
        
        {/* Debug output */}
        <div className="mt-6 p-4 bg-gray-100 rounded-lg">
          <h4 className="font-medium mb-2">Current Selection:</h4>
          <pre className="text-sm text-gray-700 whitespace-pre-wrap">
            {JSON.stringify(value, null, 2)}
          </pre>
        </div>
      </div>
    );
  }
};

// Premium locked story
export const WithPremiumLocks: Story = {
  args: {
    value: {
      type: 'single-tone',
      singleTone: {
        colorId: 'medium-brown'
      }
    },
    palette: defaultPalette,
    lockedIds: defaultPalette.filter(c => c.isPremium).map(c => c.id)
  },
  render: function PremiumLockedStory(args) {
    const [value, setValue] = React.useState<ColorChoice>(args.value);
    
    return (
      <div>
        <div className="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
          <p className="text-sm text-yellow-800">
            🔒 Premium colors are locked in this demo. In the real app, users can upgrade to unlock them.
          </p>
        </div>
        
        <ColorStylePicker
          {...args}
          value={value}
          onChange={setValue}
        />
        
        {/* Debug output */}
        <div className="mt-6 p-4 bg-gray-100 rounded-lg">
          <h4 className="font-medium mb-2">Current Selection:</h4>
          <pre className="text-sm text-gray-700 whitespace-pre-wrap">
            {JSON.stringify(value, null, 2)}
          </pre>
        </div>
      </div>
    );
  }
};

// Interactive playground
export const Playground: Story = {
  args: {
    value: {
      type: 'accented',
      accented: {
        baseColorId: 'medium-brown',
        accentColorId: 'golden-blonde',
        technique: 'highlights',
        intensity: 'standard',
        placement: 'face-framing',
        blend: 'soft'
      }
    },
    palette: defaultPalette,
    lockedIds: []
  },
  argTypes: {
    lockedIds: {
      control: 'check',
      options: defaultPalette.filter(c => c.isPremium).map(c => c.id)
    }
  },
  render: function PlaygroundStory(args) {
    const [value, setValue] = React.useState<ColorChoice>(args.value);
    
    React.useEffect(() => {
      setValue(args.value);
    }, [args.value]);
    
    return (
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Color Picker */}
        <div>
          <ColorStylePicker
            {...args}
            value={value}
            onChange={setValue}
          />
        </div>
        
        {/* Live Preview & Debug */}
        <div className="space-y-4">
          {/* Color Preview */}
          <div className="p-4 bg-white rounded-lg shadow-sm border">
            <h4 className="font-medium mb-3">Color Preview</h4>
            <div className="flex items-center space-x-3">
              {value.type === 'single-tone' && value.singleTone && (
                <div
                  className="w-16 h-16 rounded-lg border-2 border-gray-300 shadow-sm"
                  style={{ 
                    backgroundColor: defaultPalette.find(c => c.id === value.singleTone!.colorId)?.hex 
                  }}
                />
              )}
              {value.type === 'accented' && value.accented && (
                <>
                  <div
                    className="w-12 h-16 rounded-l-lg border-2 border-gray-300 shadow-sm"
                    style={{ 
                      backgroundColor: defaultPalette.find(c => c.id === value.accented!.baseColorId)?.hex 
                    }}
                  />
                  <div
                    className="w-12 h-16 rounded-r-lg border-2 border-gray-300 shadow-sm"
                    style={{ 
                      backgroundColor: defaultPalette.find(c => c.id === value.accented!.accentColorId)?.hex 
                    }}
                  />
                </>
              )}
            </div>
          </div>
          
          {/* Debug Output */}
          <div className="p-4 bg-gray-50 rounded-lg">
            <h4 className="font-medium mb-2">Debug Output:</h4>
            <pre className="text-xs text-gray-700 whitespace-pre-wrap overflow-auto">
              {JSON.stringify(value, null, 2)}
            </pre>
          </div>
        </div>
      </div>
    );
  }
};
